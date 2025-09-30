<x-layoutAdmin>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">All Notifications</h1>
      <form method="post" action="#" onsubmit="event.preventDefault(); window.markAllNotificationsAsRead && window.markAllNotificationsAsRead();">
        @csrf
        <button class="px-3 py-2 text-sm bg-blue-600 text-white rounded">Mark all as read</button>
      </form>
    </div>
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
    <div class="mt-4">{{ $notifications->links('pagination::tailwind') }}</div>
  </div>
</x-layoutAdmin>
