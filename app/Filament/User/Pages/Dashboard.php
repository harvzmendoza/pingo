<?php

namespace App\Filament\User\Pages;

use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getHeader(): ?View
    {
        $user = Filament::auth()->user();

        return view('filament.user.pages.dashboard-header', [
            'user' => $user,
            'businessName' => $user?->business_name ?: 'Your Business',
            'businessDescription' => $user?->business_description ?: 'Set your business details to personalize your dashboard and campaign identity.',
        ]);
    }
}
