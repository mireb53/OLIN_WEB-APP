<x-layoutAdmin>
    <main class="flex-1 p-6 md:p-10">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-slate-700 mb-2">Email Templates</h1>
                <p class="text-slate-500 italic">Create, edit, or delete templates. Defaults are used if no template is stored.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">
                    ← Back to Settings
                </a>
                <a href="{{ route('admin.email-templates.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-700 text-white font-semibold hover:bg-slate-800">New Template</a>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-xl p-6 shadow-md border border-gray-200">
            @if(($templates ?? collect())->isEmpty())
                <p class="text-slate-600">No templates stored yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-600">
                            <tr>
                                <th class="py-2 pr-4">Key</th>
                                <th class="py-2 pr-4">Subject</th>
                                <th class="py-2 pr-4">Active</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="align-top">
                        @foreach($templates as $t)
                            <tr class="border-t border-slate-100">
                                <td class="py-2 pr-4 font-mono text-xs">{{ $t->key }}</td>
                                <td class="py-2 pr-4">{{ $t->subject }}</td>
                                <td class="py-2 pr-4">
                                    <span class="text-xs px-2 py-1 rounded {{ $t->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700' }}">{{ $t->is_active ? 'Yes' : 'No' }}</span>
                                </td>
                                <td class="py-2">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.email-templates.edit', $t) }}" class="px-3 py-1.5 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Edit</a>
                                        <form method="POST" action="{{ route('admin.email-templates.destroy', $t) }}" onsubmit="return confirm('Delete this template?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-1.5 rounded bg-red-600 text-white hover:bg-red-700">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </main>
</x-layoutAdmin>
