<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EmailTemplateController extends Controller
{
    public function index()
    {
        // Authorization (only super admin for now)
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403);
        }

        // Placeholder: eventually load templates from DB
        $templates = [];

        return view('admin.email_templates.index', compact('templates'));
    }
}
