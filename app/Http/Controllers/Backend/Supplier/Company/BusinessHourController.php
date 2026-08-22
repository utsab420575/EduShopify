<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use Illuminate\Http\Request;

class BusinessHourController extends Controller
{
    use InteractsWithSupplierAccount;

    public function edit()
    {
        $account = $this->currentAccount();
        $businessHours = $account->businessHours()->orderBy('day_of_week')->get()->keyBy('day_of_week');

        return view('backend.supplier.company.business-hours', [
            'account' => $account,
            'user' => $this->currentUser(),
            'businessHours' => $businessHours,
            'dayNames' => BusinessHour::dayNames(),
        ]);
    }

    public function update(Request $request)
    {
        $account = $this->currentAccount();
        $days = $request->input('days', []);

        foreach (BusinessHour::dayNames() as $dayIndex => $dayName) {
            $isOpen = isset($days[$dayIndex]['is_open']);
            $openTime = $isOpen ? ($days[$dayIndex]['open_time'] ?? '09:00') : null;
            $closeTime = $isOpen ? ($days[$dayIndex]['close_time'] ?? '17:00') : null;

            BusinessHour::updateOrCreate(
                [
                    'supplier_account_id' => $account->id,
                    'day_of_week' => $dayIndex,
                ],
                [
                    'is_open' => $isOpen,
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                ]
            );
        }

        return redirect()->route('supplier.company.business-hours')->with('success', 'Business hours updated.');
    }
}
