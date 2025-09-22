<x-layoutAdmin>
  <main class="p-6 md:p-10 max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">Administrator FAQs</h1>
    <p class="text-slate-500 mb-8">Common questions and answers to help you administer the platform effectively.</p>

    <div class="space-y-6">
      @foreach($faqs as $faq)
        <div class="border border-slate-200 rounded-xl p-5 bg-white shadow-sm hover:shadow-md transition">
          <h2 class="font-semibold text-slate-800 mb-2">{{ $faq['q'] }}</h2>
          <p class="text-slate-600 leading-relaxed">{{ $faq['a'] }}</p>
        </div>
      @endforeach
    </div>

    <div class="mt-10 flex gap-4">
      <a href="{{ route('admin.help') }}" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">Back to Help Home</a>
      <a href="{{ route('admin.help.docs') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">View Documentation</a>
    </div>
  </main>
</x-layoutAdmin>
