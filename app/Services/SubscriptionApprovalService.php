<?php

namespace App\Services;

use App\Enums\SubscriptionRequestStatus;
use App\Models\SubscriptionHistory;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SubscriptionApprovalService
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function approve(SubscriptionRequest $request, User $admin): void
    {
        if (! $request->isPending()) {
            throw new RuntimeException('Only pending requests can be approved.');
        }

        DB::transaction(function () use ($request, $admin): void {
            $plan = $request->plan;
            $user = $request->user;

            SubscriptionHistory::query()
                ->where('user_id', $user->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            $subscriptionEndsAt = $plan->isFree()
                ? $this->subscriptionService->freeTrialSubscriptionEndsAt()
                : null;

            $this->subscriptionService->activateApprovedPlan(
                user: $user,
                plan: $plan,
                subscriptionEndsAt: $subscriptionEndsAt,
            );

            SubscriptionHistory::query()->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'subscription_request_id' => $request->id,
                'started_at' => now(),
                'ended_at' => null,
            ]);

            $request->update([
                'status' => SubscriptionRequestStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        });
    }

    public function reject(SubscriptionRequest $request, User $admin, ?string $adminNote): void
    {
        if (! $request->isPending()) {
            throw new RuntimeException('Only pending requests can be rejected.');
        }

        $request->update([
            'status' => SubscriptionRequestStatus::Rejected,
            'admin_note' => $adminNote,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);
    }
}
