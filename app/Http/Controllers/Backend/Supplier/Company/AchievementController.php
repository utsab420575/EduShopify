<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\AccountAchievement;

class AchievementController extends Controller
{
    use InteractsWithSupplierAccount;

    public function request(Achievement $achievement)
    {
        $account = $this->currentAccount();

        if ($account->accountAchievements()->where('achievement_id', $achievement->id)->exists()) {
            return redirect()->route('supplier.company.profile', ['section' => 'achievements']);
        }

        abort_unless($achievement->is_active, 404);

        $account->accountAchievements()->create([
            'achievement_id' => $achievement->id,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'achievements'])
            ->with('success', 'Achievement request submitted for review.');
    }

    /**
     * Withdraw a claim that hasn't been approved — a pending request no
     * longer wanted, or a rejected one being cleared so the achievement can
     * be requested again (request() blocks re-requesting while any claim
     * row, of any status, still exists).
     */
    public function undo(AccountAchievement $accountAchievement)
    {
        abort_unless($accountAchievement->account_id === $this->currentAccount()->id, 404);
        abort_unless(in_array($accountAchievement->status, ['pending', 'rejected'], true), 422);

        $accountAchievement->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'achievements'])
            ->with('success', 'Achievement request withdrawn.');
    }
}
