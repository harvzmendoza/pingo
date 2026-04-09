@php
    $user = auth()->user();
@endphp

<div class="flex items-center justify-between">
    <div>
        <h1 class="text-4xl font-bold text-gray-900">Welcome, {{ $user->name }}!</h1>
    </div>
</div>

<section class="dashboard-business-hero">
    <div>
        <p class="dashboard-business-kicker">Business Overview</p>
        <h1 class="dashboard-business-title">{{ $businessName }}</h1>
        <p class="dashboard-business-description">{{ $businessDescription }}</p>
    </div>

    <div class="dashboard-business-pill">
        <span class="dashboard-business-dot"></span>
        Live Dashboard
    </div>
</section>
