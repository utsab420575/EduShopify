<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\UpdateBusinessHoursRequest;
use App\Models\BusinessHour;

class BusinessHourController extends Controller
{
    use InteractsWithSupplierAccount;

    public function update(UpdateBusinessHoursRequest $request)
    {
        $account = $this->currentAccount();
        $days = $request->validated()['days'];

        foreach (range(0, 6) as $day) {
            $row = $days[$day] ?? [];
            $isOpen = (bool) ($row['is_open'] ?? false);

            BusinessHour::updateOrCreate(
                ['supplier_account_id' => $account->id, 'account_location_id' => null, 'day_of_week' => $day],
                [
                    'is_open' => $isOpen,
                    'open_time' => $isOpen ? ($row['open_time'] ?? '09:00') : null,
                    'close_time' => $isOpen ? ($row['close_time'] ?? '17:00') : null,
                ]
            );
        }

        return redirect()->route('supplier.company.profile', ['section' => 'hours'])
            ->with('success', 'Business hours saved.');
    }
}
