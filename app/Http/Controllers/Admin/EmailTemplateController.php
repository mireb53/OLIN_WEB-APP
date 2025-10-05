<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class EmailTemplateController extends Controller
{
    protected function authorizeSuperAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->authorizeSuperAdmin();
        if (!Schema::hasTable('email_templates')) {
            $templates = collect();
            return view('admin.email_templates.index', compact('templates'))
                ->with('status', 'Note: email_templates table is missing. Please run migrations to enable Email Template management.');
        }
        $templates = EmailTemplate::orderBy('key')->get();
        return view('admin.email_templates.index', compact('templates'));
    }

    public function create()
    {
        $this->authorizeSuperAdmin();
        return view('admin.email_templates.create');
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();
        $data = $request->validate([
            'key' => ['required','string','max:100', Rule::in(['verify_email','admin_verification','general_notification'])],
            'subject' => 'required|string|max:200',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['school_id'] = null; // global for now
        EmailTemplate::updateOrCreate([
            'key' => $data['key'],
            'school_id' => null,
        ], $data);
        return redirect()->route('admin.email-templates.index')->with('status', 'Template saved.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $this->authorizeSuperAdmin();
        return view('admin.email_templates.edit', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $this->authorizeSuperAdmin();
        $data = $request->validate([
            'key' => [
                'required','string','max:100',
                Rule::in(['verify_email','admin_verification','general_notification']),
                Rule::unique('email_templates','key')
                    ->where(fn($q) => $q->whereNull('school_id'))
                    ->ignore($emailTemplate->id),
            ],
            'subject' => 'required|string|max:200',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $emailTemplate->update($data);
        return redirect()->route('admin.email-templates.index')->with('status', 'Template updated.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $this->authorizeSuperAdmin();
        $emailTemplate->delete();
        return redirect()->route('admin.email-templates.index')->with('status', 'Template deleted.');
    }
}
