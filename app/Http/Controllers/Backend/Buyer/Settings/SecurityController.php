<?php

namespace App\Http\Controllers\Backend\Buyer\Settings;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Settings\UpdateSecurityRequest;
use App\Models\Currency;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    use InteractsWithBuyerAccount;

    public function edit()
    {
        return view('backend.buyer.settings.security', [
            'currencies' => Currency::active()->orderBy('code')->get(['code', 'name']),
        ]);
    }

    public function update(UpdateSecurityRequest $request)
    {
        $user = $this->currentUser();

        $data = $request->safe()->only(['name', 'email', 'phone', 'locale', 'currency_code']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->string('password'));
        }

        $user->update($data);

        return back()->with('success', 'Settings updated.');
    }
}
