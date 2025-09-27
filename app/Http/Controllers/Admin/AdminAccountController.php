<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminAccountController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        return view('admin.admin_account', compact('admin'));
    }
}
