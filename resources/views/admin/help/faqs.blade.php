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
  <!-- Help routes removed: route('admin.help') and route('admin.help.docs') no longer exist -->
    </div>
  </main>
</x-layoutAdmin>
