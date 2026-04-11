<?php

namespace App\Filament\Resources\SubscriptionRequests\Pages;

use App\Filament\Resources\SubscriptionRequests\SubscriptionRequestResource;
use App\Models\User;
use App\Services\SubscriptionApprovalService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscriptionRequest extends ViewRecord
{
    protected static string $resource = SubscriptionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->isPending())
                ->requiresConfirmation()
                ->modalHeading('Approve subscription')
                ->modalDescription('This will activate the plan for the customer and close any previous active period in history.')
                ->action(function (SubscriptionApprovalService $approvalService): void {
                    $admin = Filament::auth()->user();
                    if (! $admin instanceof User) {
                        return;
                    }

                    $approvalService->approve($this->getRecord(), $admin);

                    Notification::make()
                        ->success()
                        ->title('Subscription approved')
                        ->send();

                    $this->redirect(static::getUrl(['record' => $this->getRecord()]));
                }),
            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->visible(fn (): bool => $this->getRecord()->isPending())
                ->schema([
                    Textarea::make('admin_note')
                        ->label('Note (optional)')
                        ->maxLength(2000),
                ])
                ->action(function (array $data, SubscriptionApprovalService $approvalService): void {
                    $admin = Filament::auth()->user();
                    if (! $admin instanceof User) {
                        return;
                    }

                    $approvalService->reject($this->getRecord(), $admin, $data['admin_note'] ?? null);

                    Notification::make()
                        ->success()
                        ->title('Request rejected')
                        ->send();

                    $this->redirect(static::getUrl(['record' => $this->getRecord()]));
                }),
        ];
    }
}
