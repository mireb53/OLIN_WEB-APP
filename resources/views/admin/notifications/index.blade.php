<x-layoutAdmin>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Notifications</h1>
    <div class="bg-white rounded shadow divide-y">
      @forelse($notifications as $n)
        <div class="p-4 flex items-start gap-3 {{ $n->is_read ? '' : 'bg-blue-50' }}">
          <div class="notification-icon {{ $n->type }}">
            @php
              $icon = match($n->type){
                'new_registration' => 'user-plus',
                'security_alert' => 'shield-alt',
                'system_alert' => 'exclamation-triangle',
                default => 'bell'
              };
            @endphp
            <i class="fas fa-{{ $icon }} text-white text-sm"></i>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-800">{{ $n->title ?? 'Notification' }}</p>
            <p class="text-sm text-gray-600">{{ $n->message }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $n->created_at->format('M d, Y h:i A') }}</p>
          </div>
        </div>
      @empty
        <div class="p-6 text-gray-500">No notifications found.</div>
      @endforelse
    </div>
  </div>
</x-layoutAdmin>
