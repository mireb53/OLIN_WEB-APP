<x-layoutAdmin>
  <main class="p-6 md:p-10 max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">Contact IT Support</h1>
    <p class="text-slate-500 mb-8">Choose a preferred support channel. Critical incidents should use the priority hotline.</p>

    <div class="space-y-6">
      @foreach($channels as $ch)
        <div class="border border-slate-200 rounded-xl p-5 bg-white shadow-sm hover:shadow-md transition">
          <h2 class="font-semibold text-slate-800 mb-1">{{ $ch['name'] }}</h2>
          <p class="text-slate-600 text-sm mb-2">Contact: <span class="font-mono">{{ $ch['contact'] }}</span></p>
          <span class="inline-block text-xs px-2 py-1 rounded bg-slate-100 text-slate-600">SLA: {{ $ch['sla'] }}</span>
        </div>
      @endforeach
    </div>

    <div class="mt-10 flex gap-4">
  <!-- Help routes removed: route('admin.help') and route('admin.help.docs') no longer exist -->
    </div>
  </main>
</x-layoutAdmin>
