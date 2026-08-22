<?php

namespace App\Http\Controllers\Backend\Supplier\Billing;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        $payments = $account->subscriptionPayments()->latest('paid_at')->paginate(10);

        return view('backend.supplier.subscription.payments', [
            'account' => $account,
            'user' => $this->currentUser(),
            'payments' => $payments,
        ]);
    }
}
