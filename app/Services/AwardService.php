<?php

namespace App\Services;

use App\Models\Award;
use App\Models\Quotation;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\DashboardNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Buyer-side award creation and cancellation. Supplier accept/reject (which
 * flips the RFQ and quotation to their final 'awarded' state and creates the
 * Purchase Order) is a supplier-dashboard action and lives in
 * AwardResponseService instead.
 */
class AwardService
{
    public function __construct(private QuotationActivityService $quotationActivities)
    {
    }

    public function create(Quotation $quotation, User $user, ?string $note = null): Award
    {
        return DB::transaction(function () use ($quotation, $user, $note) {
            $rfq = $quotation->rfq()->lockForUpdate()->first();

            if ($rfq->awards()->where('status', 'pending_supplier_response')->exists()) {
                throw ValidationException::withMessages(['award' => 'An award is already awaiting a supplier response for this RFQ.']);
            }

            $attemptNo = ($rfq->awards()->max('award_attempt_no') ?? 0) + 1;

            $award = Award::create([
                'award_number'         => $this->generateAwardNumber(),
                'rfq_id'               => $rfq->id,
                'quotation_id'         => $quotation->id,
                'buyer_account_id'     => $rfq->buyer_account_id,
                'supplier_account_id'  => $quotation->supplier_account_id,
                'awarded_by_user_id'   => $user->id,
                'award_attempt_no'     => $attemptNo,
                'status'               => 'pending_supplier_response',
                'award_note'           => $note,
                'response_deadline'    => now()->addHours((int) Setting::get('award', 'award_response_hours', 72)),
                'awarded_at'           => now(),
            ]);

            $rfq->update(['status' => 'award_pending']);

            $this->quotationActivities->record($quotation, 'awarded', $note, 'buyer', $user->id);

            $this->notifySupplier($award, "Congratulations — your quotation {$quotation->quotation_number} for \"{$rfq->title}\" has been awarded. Please respond within the deadline.");

            return $award;
        });
    }

    /**
     * "Undo Award" — the buyer pulls back a still-pending award before the
     * supplier has responded. Reopens the RFQ (mirrors AwardResponseService
     * ::reject()'s exact rfq-status logic) and leaves the quotation's own
     * status untouched, since create() never changed it in the first place
     * (only accept() promotes a quotation to 'awarded').
     */
    public function cancel(Award $award, ?string $reason = null): Award
    {
        return DB::transaction(function () use ($award, $reason) {
            $award = Award::whereKey($award->id)->lockForUpdate()->firstOrFail();

            if (! $award->isAwaitingResponse()) {
                throw ValidationException::withMessages(['status' => 'This award has already been responded to.']);
            }

            $award->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $rfq = $award->rfq;
            $rfq->update(['status' => $rfq->deadlinePassed() ? 'closed' : 'open']);

            $this->quotationActivities->record($award->quotation, 'award_cancelled', $reason, 'buyer', Auth::id());

            $this->notifySupplier($award, "The buyer cancelled the award for \"{$rfq->title}\".");

            return $award->fresh();
        });
    }

    private function generateAwardNumber(): string
    {
        $year   = date('Y');
        $latest = Award::where('award_number', 'like', "AWD-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "AWD-{$year}-{$seq}";
    }

    private function notifySupplier(Award $award, string $message): void
    {
        $users = User::whereHas('accountMember', fn ($q) => $q->where('account_id', $award->supplier_account_id)->where('status', 'active'))->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DashboardNotification($message, route('supplier.awards.show', $award)));
        }
    }
}
