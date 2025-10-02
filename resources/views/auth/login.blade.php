<x-layouts.auth :title="'Login - OLIN'">
  <div class="container" id="login-page">
    
    {{-- Left Section --}}
    <div class="left-section">
      <div class="logo flex justify-center items-center mb-4">
        <img src="{{ asset('images/Full-Logo-OLIN.png') }}" 
             alt="OLIN Logo" 
             class="w-40 h-40 object-contain md:w-56 md:h-56">
      </div>
      <div class="tagline">Empowering Offline Learning</div>
      <div class="illustration-container">
        <img src="{{ asset('images/placeholder.jpg') }}" alt="Login Illustration">
      </div>
    </div>

    {{-- Right Section --}}
    <div class="right-section">
      <div class="form-container">
        <h1 class="welcome-title">Welcome Back!</h1>
        <p class="welcome-subtitle">Manage your courses and empower offline learning.</p>

        {{-- Flash messages --}}
        @if(session('status'))
          <div class="text-green-600 mb-3" role="status">{{ session('status') }}</div>
        @endif
        @if(session('error'))
          <div class="text-red-500 mb-3" role="alert">{{ session('error') }}</div>
        @endif

        {{-- Validation error (first) --}}
        @if($errors->any())
          <div class="text-red-500 mb-3" role="alert">
            {{ $errors->first() }}
          </div>
        @endif

        {{-- Login Form --}}
        <form action="{{ route('login') }}" method="POST">
          @csrf

          {{-- Email --}}
          <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
          </div>

          {{-- Password with Toggle --}}
          <div class="form-group password-group">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">👁</button>
          </div>

          {{-- Forgot Password --}}
          <a href="#" class="forgot-password">Forgot Password?</a>

          {{-- Submit --}}
          <button type="submit" class="btn login-btn">Login</button>

          {{-- OR Separator --}}
          <div class="separator">
            <span>or</span>
          </div>

          {{-- Google Login --}}
          <a href="{{ route('socialite.google.redirect') }}" class="btn google-login">
            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo">
            Sign in with Google
          </a>
        </form>

        {{-- Registration disabled notice --}}
        <div class="signup-link">
          Need an account? Please contact your administrator.
        </div>
      </div>
    </div>

  </div>
  {{-- Minimal password toggle fallback --}}
  <script>
    window.togglePassword = window.togglePassword || function(inputId, btn){
      try {
        const el = document.getElementById(inputId);
        if (!el) return;
        const isPwd = el.type === 'password';
        el.type = isPwd ? 'text' : 'password';
        if (btn) btn.setAttribute('aria-pressed', isPwd ? 'true' : 'false');
      } catch(e) { /* noop */ }
    };
  </script>
</x-layouts.auth>
