<?php

namespace App\Traits;

use App\Models\UserInvestment;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait AutoMaturity
{
    /**
     * Check for matured investments and settle them.
     */
    public function checkMaturity($user)
    {
        // Find investments that have reached end_at and are still 'active'
        $matured = $user->investments()
            ->where('status', 'active')
            ->where('end_at', '<=', now())
            ->get();

        if ($matured->isEmpty()) {
            return;
        }

        $settledCount = 0;
        $totalProfit = 0;

        DB::transaction(function () use ($user, $matured, &$settledCount, &$totalProfit) {
            foreach ($matured as $investment) {
                // Update investment status
                $investment->update(['status' => 'completed']);

                // Create profit transaction
                $user->transactions()->create([
                    'currency' => 'MYR',
                    'type' => 'profit',
                    'status' => 'approved',
                    'amount' => $investment->target_return,
                    'note' => "ROI Kematangan: {$investment->plan_name}",
                ]);

                // Increment wallet balance
                $user->wallet->increment('balance', $investment->target_return);

                $settledCount++;
                $totalProfit += $investment->target_return;
            }
        });

        if ($settledCount > 0) {
            session()->flash('ok', "Tahniah! {$settledCount} pelaburan telah matang dan RM " . number_format($totalProfit, 2) . " telah dikreditkan ke baki anda.");
            Log::info("User ID {$user->id} settled {$settledCount} investments via Trigger.");
        }
    }
}
