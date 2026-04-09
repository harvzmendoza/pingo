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
use Livewire\Component;

class TutorialHelpButton extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public function openTutorialAction(): Action
    {
        return Action::make('openTutorial')
            ->modalHeading('Quick Start Tutorial')
            ->modalDescription('Follow these steps to learn the basic workflow in your user panel.')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->schema([
                Wizard::make([
                    Step::make('Add contacts')
                        ->schema([
                            Placeholder::make('step_add_contacts')
                                ->hiddenLabel()
                                ->content('Go to Contacts, then click Create contact and save customer details.'),
                        ]),
                    Step::make('Create message')
                        ->schema([
                            Placeholder::make('step_create_message')
                                ->hiddenLabel()
                                ->content('Open Messages, create your SMS template, and keep content concise and actionable.'),
                        ]),
                    Step::make('Send campaign')
                        ->schema([
                            Placeholder::make('step_send_campaign')
                                ->hiddenLabel()
                                ->content('Use Send Campaign to choose recipients and dispatch your message in seconds.'),
                        ]),
                    Step::make('Track performance')
                        ->schema([
                            Placeholder::make('step_track_performance')
                                ->hiddenLabel()
                                ->content('Review dashboard charts and stats to monitor sent, failed, and campaign trends.'),
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
