<x-layoutAdmin>
    <main class="flex-1 p-6 md:p-10">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-700">Edit Email Template</h1>
        </div>

        <form method="POST" action="{{ route('admin.email-templates.update', $emailTemplate) }}" class="bg-white rounded-xl p-6 shadow-md border border-gray-200 max-w-3xl">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-slate-700 font-semibold mb-1">Key <span class="text-red-600">*</span></label>
                <select name="key" class="w-full border rounded-lg px-3 py-2" required>
                    <option value="verify_email" {{ old('key', $emailTemplate->key)==='verify_email' ? 'selected' : '' }}>verify_email</option>
                    <option value="admin_verification" {{ old('key', $emailTemplate->key)==='admin_verification' ? 'selected' : '' }}>admin_verification</option>
                    <option value="general_notification" {{ old('key', $emailTemplate->key)==='general_notification' ? 'selected' : '' }}>general_notification</option>
                </select>
                @error('key')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-slate-700 font-semibold mb-1">Subject <span class="text-red-600">*</span></label>
                <input name="subject" value="{{ old('subject', $emailTemplate->subject) }}" class="w-full border rounded-lg px-3 py-2" required />
                @error('subject')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-slate-700 font-semibold mb-1">HTML Body <span class="text-red-600">*</span></label>
                <textarea name="body_html" rows="12" class="w-full border rounded-lg px-3 py-2" required>{{ old('body_html', $emailTemplate->body_html) }}</textarea>
                @error('body_html')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-slate-700 font-semibold mb-1">Plain Text Body (optional)</label>
                <textarea name="body_text" rows="6" class="w-full border rounded-lg px-3 py-2">{{ old('body_text', $emailTemplate->body_text) }}</textarea>
                @error('body_text')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="h-4 w-4" {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }} />
                <label for="is_active" class="text-slate-700">Active</label>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-800">Update</button>
                <a href="{{ route('admin.email-templates.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </main>
</x-layoutAdmin>
