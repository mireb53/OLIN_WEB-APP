<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'OLIN' }}</title>
  @vite(['resources/css/login.css', 'resources/js/login.js'])
</head>
<body>
  <div class="auth-wrapper">
    {{ $slot }}
  </div>
</body>
</html>
