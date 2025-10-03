<x-layouts.auth :title="'Forgot Password - OLIN'">
  <div class="container max-w-md mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">Forgot Password</h1>
    @if (session('status'))
      <div class="text-green-600 mb-4">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
      @csrf
      <input type="email" name="email" placeholder="Email" class="w-full border rounded px-3 py-2" required>
      @error('email')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
      <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">Send Password Reset Link</button>
    </form>
    <div class="mt-4">
      <a class="text-indigo-600" href="{{ route('login') }}">Back to login</a>
    </div>
  </div>
</x-layouts.auth>
