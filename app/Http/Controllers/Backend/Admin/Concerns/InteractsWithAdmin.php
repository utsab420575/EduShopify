<?php

namespace App\Http\Controllers\Backend\Admin\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait InteractsWithAdmin
{
    protected function admin(): User
    {
        return Auth::user();
    }
}
