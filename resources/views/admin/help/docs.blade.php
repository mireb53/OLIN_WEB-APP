<x-layoutAdmin>
  <main class="p-6 md:p-10 max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">System Documentation</h1>
    <p class="text-slate-500 mb-8">Technical and operational documentation for core system modules.</p>

    <div class="grid gap-6 md:grid-cols-2">
      @foreach($docs as $doc)
        <div class="border border-slate-200 rounded-xl p-5 bg-white shadow-sm hover:shadow-md transition flex flex-col">
          <h2 class="font-semibold text-slate-800 mb-2">{{ $doc['title'] }}</h2>
          <p class="text-xs text-slate-500 mb-4">Updated {{ $doc['updated']->diffForHumans() }}</p>
          <div class="mt-auto">
            <button class="px-3 py-2 text-sm rounded-md bg-slate-800 text-white hover:bg-slate-900">Open Guide</button>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-10 flex gap-4">
      <a href="{{ route('admin.help') }}" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">Back to Help Home</a>
      <a href="{{ route('admin.help.support') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Contact Support</a>
    </div>
  </main>
</x-layoutAdmin>
