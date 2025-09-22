<x-layoutAdmin>
    @push('page_assets')
        @vite(['resources/css/admin/help.css'])
    @endpush

    <x-slot name="header">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-slate-800 mb-2">Help & Support (Admin)</h1>
            <p class="text-lg text-slate-500">
                Access comprehensive guides, troubleshooting, and support resources for OLIN system administration.
            </p>
        </div>
    </x-slot>

    <!-- Search Documentation -->
    <div class="mb-8">
        <div class="relative max-w-lg">
            <input type="text"
                   class="w-full p-4 pr-12 pl-5 border-2 border-slate-200 rounded-xl text-base outline-none transition-all duration-300 ease-in-out bg-white focus:border-slate-900 focus:shadow-[0_0_0_3px_rgba(15,23,42,0.1)] focus:-translate-y-px"
                   placeholder="Search Admin Documentation...">
            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">🔍</span>
        </div>
    </div>

    <!-- Quick Navigation -->
    <div class="mb-10 flex flex-wrap gap-4">
        <a href="{{ route('admin.help.faqs') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-semibold shadow">Admin FAQs</a>
        <a href="{{ route('admin.help.docs') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-900 text-sm font-semibold shadow">System Documentation</a>
        <a href="{{ route('admin.help.support') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold shadow">IT Support</a>
    </div>

    <!-- Section 1: Admin Guides & Manuals -->
    <section class="bg-white rounded-2xl p-8 mb-8 shadow-[0_8px_30px_rgba(0,0,0,0.08)] border border-slate-200">
        <h2 class="text-xl font-bold text-slate-900 mb-6 uppercase tracking-[0.5px] pb-2 border-b-2 border-slate-100">
            Section 1: Admin Guides & Manuals
        </h2>

        <div class="guide-grid">
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">System Setup & Configuration</div>
                <button class="guide-button">Read Guide</button>
            </div>
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">Managing User Roles</div>
                <button class="guide-button">Read Guide</button>
            </div>
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">Data Backup & Restore</div>
                <button class="guide-button">Read Guide</button>
            </div>
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">Storage Management</div>
                <button class="guide-button">Read Guide</button>
            </div>
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">Sync Troubleshooting</div>
                <button class="guide-button">Read Guide</button>
            </div>
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">Security Best Practices</div>
                <button class="guide-button">Read Guide</button>
            </div>
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">Performance Monitoring</div>
                <button class="guide-button">Read Guide</button>
            </div>
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">API Management</div>
                <button class="guide-button">Read Guide</button>
            </div>
            <div class="guide-card">
                <div class="text-lg font-semibold text-slate-800 mb-6">Troubleshooting Guide</div>
                <button class="guide-button">Read Guide</button>
            </div>
        </div>
    </section>

    <!-- Section 2: Troubleshooting & FAQs -->
    <section class="bg-white rounded-2xl p-8 mb-8 shadow-[0_8px_30px_rgba(0,0,0,0.08)] border border-slate-200">
        <h2 class="text-xl font-bold text-slate-900 mb-6 uppercase tracking-[0.5px] pb-2 border-b-2 border-slate-100">
            Section 2: Troubleshooting & FAQs
        </h2>

        <ul class="list-none mt-6">
            <li class="faq-item">Why are syncs failing for some users?</li>
            <li class="faq-item">How to add a new storage provider?</li>
            <li class="faq-item">My dashboard metrics are not updating.</li>
            <li class="faq-item">How to reset user passwords in bulk?</li>
            <li class="faq-item">System storage is running low - what to do?</li>
            <li class="faq-item">How to configure two-factor authentication?</li>
        </ul>

        <a href="#"
           class="inline-block mt-6 text-slate-900 no-underline font-semibold transition-all duration-300 ease-in-out uppercase tracking-[0.5px] hover:text-slate-800 hover:underline">
            View All Troubleshooting Guides
        </a>
    </section>

    <!-- Section 3: Contact Technical Support -->
    <section class="bg-white rounded-2xl p-8 mb-8 shadow-[0_8px_30px_rgba(0,0,0,0.08)] border border-slate-200">
        <h2 class="text-xl font-bold text-slate-900 mb-6 uppercase tracking-[0.5px] pb-2 border-b-2 border-slate-100">
            Section 3: Contact Technical Support
        </h2>

        <p class="text-slate-500 text-base mb-4">
            For critical issues that require immediate attention, contact our dedicated technical support team.
        </p>

        <div class="flex flex-col md:flex-row gap-4 mt-6">
            <a href="#" class="support-button">Open Support Ticket</a>
            <a href="#" class="support-button secondary">View System Status</a>
            <a href="#" class="support-button secondary">Live Chat Support</a>
        </div>
    </section>
</x-layoutAdmin>
