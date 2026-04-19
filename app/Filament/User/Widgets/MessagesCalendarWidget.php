<?php

namespace App\Filament\User\Widgets;

use App\Jobs\SendScheduledCampaignJob;
use App\Models\Contact;
use App\Models\Message;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Str;
use Throwable;

class MessagesCalendarWidget extends CalendarWidget
{
    protected string|\Illuminate\Support\HtmlString|null|bool $heading = 'Messages & queue';

    protected bool $eventClickEnabled = true;

    /**
     * @var array<int, array{scheduled_for: string, recipients: string}>
     */
    protected array $scheduledDetailsByMessageId = [];

    /**
     * @return Collection<int, CalendarEvent>
     */
    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $userId = auth()->id();

        if ($userId === null) {
            return collect();
        }

        $this->scheduledDetailsByMessageId = [];
        $queuedEvents = $this->queuedCampaignCalendarEvents($info, $userId);
        $scheduledMessageIds = $queuedEvents
            ->pluck('message_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $messages = Message::query()
            ->where('user_id', $userId)
            ->where(function (Builder $query) use ($info, $scheduledMessageIds): void {
                $query
                    ->whereBetween('created_at', [$info->start, $info->end])
                    ->orWhereIn('id', $scheduledMessageIds);
            })
            ->get();

        $messageEvents = $messages
            ->reject(fn (Message $message): bool => in_array($message->id, $scheduledMessageIds, true))
            ->map(function (Message $message): CalendarEvent {
                $preview = Str::limit((string) $message->content, 40);

                return CalendarEvent::make($message)
                    ->title('Sent now: '.$preview)
                    ->start($message->created_at ?? now())
                    ->end(($message->created_at ?? now())->copy()->addMinutes(30))
                    ->backgroundColor('#4f46e5')
                    ->textColor('#ffffff')
                    ->action('view');
            });

        return $messageEvents->concat($queuedEvents->pluck('event'));
    }

    protected function messageSchema(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('content')
                    ->columnSpanFull(),
                TextEntry::make('scheduled_for')
                    ->label('Scheduled send time')
                    ->state(fn (Message $record): ?string => $this->scheduledDetailsByMessageId[$record->id]['scheduled_for'] ?? null)
                    ->visible(fn (Message $record): bool => isset($this->scheduledDetailsByMessageId[$record->id])),
                TextEntry::make('scheduled_recipients')
                    ->label('Scheduled recipients')
                    ->state(fn (Message $record): ?string => $this->scheduledDetailsByMessageId[$record->id]['recipients'] ?? null)
                    ->visible(fn (Message $record): bool => isset($this->scheduledDetailsByMessageId[$record->id]))
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }

    /**
     * @return Collection<int, array{event: CalendarEvent, message_id: int}>
     */
    private function queuedCampaignCalendarEvents(FetchInfo $info, int $userId): Collection
    {
        if (! SchemaFacade::hasTable('jobs')) {
            return collect();
        }

        $events = collect();

        $jobs = DB::table('jobs')->select(['id', 'payload', 'available_at'])->get();

        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);

            if (! is_array($payload)) {
                continue;
            }

            if (($payload['displayName'] ?? null) !== SendScheduledCampaignJob::class) {
                continue;
            }

            $command = $payload['data']['command'] ?? null;

            if (! is_string($command)) {
                continue;
            }

            try {
                $instance = unserialize($command, [
                    'allowed_classes' => [
                        SendScheduledCampaignJob::class,
                    ],
                ]);
            } catch (Throwable) {
                continue;
            }

            if (! $instance instanceof SendScheduledCampaignJob) {
                continue;
            }

            $message = Message::query()
                ->where('user_id', $userId)
                ->whereKey($instance->messageId)
                ->first();

            if (! $message instanceof Message) {
                continue;
            }

            $runAt = Carbon::createFromTimestamp((int) $job->available_at);

            if ($runAt->lt($info->start) || $runAt->gt($info->end)) {
                continue;
            }

            $recipients = $this->resolveRecipientsLabel($userId, $instance->contactIds);
            $preview = Str::limit((string) $message->content, 40);
            $this->scheduledDetailsByMessageId[$message->id] = [
                'scheduled_for' => $runAt->toDayDateTimeString(),
                'recipients' => $recipients,
            ];

            $events->push(
                [
                    'message_id' => $message->id,
                    'event' => CalendarEvent::make($message)
                        ->title('Scheduled: '.$preview)
                        ->start($runAt)
                        ->end($runAt->copy()->addMinutes(30))
                        ->backgroundColor('#d97706')
                        ->textColor('#ffffff')
                        ->action('view')
                        ->extendedProps([
                            'kind' => 'queued_campaign',
                            'job_id' => $job->id,
                        ]),
                ]
            );
        }

        return $events;
    }

    /**
     * @param  array<int>  $contactIds
     */
    private function resolveRecipientsLabel(int $userId, array $contactIds): string
    {
        $ids = collect($contactIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            $count = Contact::query()
                ->where('user_id', $userId)
                ->count();

            return "All contacts ({$count})";
        }

        $names = Contact::query()
            ->where('user_id', $userId)
            ->whereIn('id', $ids->all())
            ->orderBy('name')
            ->pluck('name')
            ->all();

        if ($names === []) {
            return 'Selected contacts';
        }

        return implode(', ', $names);
    }
}
