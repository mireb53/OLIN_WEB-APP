<x-layoutAdmin>
    <main class="flex-1 p-6 md:p-10">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-700">New Email Template</h1>
        </div>

        <form method="POST" action="{{ route('admin.email-templates.store') }}" class="bg-white rounded-xl p-6 shadow-md border border-gray-200 max-w-3xl">
            @csrf
            <div class="mb-4">
                <label class="block text-slate-700 font-semibold mb-1">Key <span class="text-red-600">*</span></label>
                <select name="key" class="w-full border rounded-lg px-3 py-2" required>
                    <option value="" disabled {{ old('key') ? '' : 'selected' }}>Select a key…</option>
                    <option value="verify_email" {{ old('key')==='verify_email' ? 'selected' : '' }}>verify_email</option>
                    <option value="admin_verification" {{ old('key')==='admin_verification' ? 'selected' : '' }}>admin_verification</option>
                    <option value="general_notification" {{ old('key')==='general_notification' ? 'selected' : '' }}>general_notification</option>
                </select>
                @error('key')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-slate-700 font-semibold mb-1">Subject <span class="text-red-600">*</span></label>
                <input name="subject" value="{{ old('subject') }}" class="w-full border rounded-lg px-3 py-2" required />
                @error('subject')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-slate-700 font-semibold mb-1">HTML Body <span class="text-red-600">*</span></label>
                <textarea name="body_html" rows="12" class="w-full border rounded-lg px-3 py-2" placeholder="Use variables like @{{ app_name }}, @{{ user_name }}, @{{ verification_code }}, @{{ verify_url }}" required>{{ old('body_html') }}</textarea>
                @error('body_html')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-slate-700 font-semibold mb-1">Plain Text Body (optional)</label>
                <textarea name="body_text" rows="6" class="w-full border rounded-lg px-3 py-2">{{ old('body_text') }}</textarea>
                @error('body_text')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="h-4 w-4" checked />
                <label for="is_active" class="text-slate-700">Active</label>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-800">Save</button>
                <a href="{{ route('admin.email-templates.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </main>
</x-layoutAdmin>
