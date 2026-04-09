<?php

namespace App\Livewire\Filament\User;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class TutorialHelpButton extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public function openTutorialAction(): Action
    {
        return Action::make('openTutorial')
            ->modalHeading('Messaging Quick Start')
            ->modalDescription('Follow the guided flow and review policy before sending campaigns.')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->schema([
                Wizard::make([
                    Step::make('Add contacts')
                        ->schema([
                            Placeholder::make('step_add_contacts')
                                ->hiddenLabel()
                                ->content(new HtmlString('<div class="tutorial-step-content tutorial-step-blue"><div class="tutorial-step-heading"><span class="tutorial-step-icon">👥</span><h4>Add contacts</h4></div><p>Go to <strong>Contacts</strong>, click <strong>Create contact</strong>, and store valid recipient numbers.</p></div>')),
                        ]),
                    Step::make('Create message')
                        ->schema([
                            Placeholder::make('step_create_message')
                                ->hiddenLabel()
                                ->content(new HtmlString('<div class="tutorial-step-content tutorial-step-indigo"><div class="tutorial-step-heading"><span class="tutorial-step-icon">✍️</span><h4>Create message</h4></div><p>Open <strong>Messages</strong>, build your SMS template, and keep content clear and concise.</p></div>')),
                        ]),
                    Step::make('Message policy')
                        ->schema([
                            Placeholder::make('step_message_policy')
                                ->hiddenLabel()
                                ->content(new HtmlString('<div class="tutorial-step-content tutorial-step-rose"><div class="tutorial-step-heading"><span class="tutorial-step-icon">🛡️</span><h4>Review message policy</h4></div><p>Links, domains, shortened URLs, and profanity are prohibited and may cause credit penalties per recipient.</p><p><a href="/user/message-policy" class="tutorial-policy-link">Open Message Policy</a></p></div>')),
                        ]),
                    Step::make('Send campaign')
                        ->schema([
                            Placeholder::make('step_send_campaign')
                                ->hiddenLabel()
                                ->content(new HtmlString('<div class="tutorial-step-content tutorial-step-amber"><div class="tutorial-step-heading"><span class="tutorial-step-icon">🚀</span><h4>Send campaign</h4></div><p>Use <strong>Send Campaign</strong> to select recipients and dispatch your approved message.</p></div>')),
                        ]),
                    Step::make('Track performance')
                        ->schema([
                            Placeholder::make('step_track_performance')
                                ->hiddenLabel()
                                ->content(new HtmlString('<div class="tutorial-step-content tutorial-step-emerald"><div class="tutorial-step-heading"><span class="tutorial-step-icon">📊</span><h4>Track performance</h4></div><p>Review dashboard charts and stats to monitor sent, failed, and overall campaign trends.</p></div>')),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }

    public function render()
    {
        return view('livewire.filament.user.tutorial-help-button');
    }
}
