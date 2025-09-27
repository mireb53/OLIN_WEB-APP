<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    body{margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background:#f4f6f8}
    .container{max-width:640px;margin:32px auto;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e6e9ee}
    .header{background:linear-gradient(90deg,#0f172a,#374151);color:#fff;padding:20px;text-align:center}
    .content{padding:28px;color:#0f172a}
    .code-box{display:block;margin:18px auto;padding:18px 24px;background:#0f172a;color:#fff;font-size:28px;font-weight:700;text-align:center;letter-spacing:4px;border-radius:8px;width:fit-content}
    .note{margin-top:12px;color:#6b7280;font-size:14px}
    .footer{padding:18px;text-align:center;color:#9ca3af;font-size:13px;background:#fafafa}
    .btn{display:inline-block;padding:10px 16px;background:#0f172a;color:#fff;border-radius:8px;text-decoration:none;margin-top:16px}
    @media (max-width:480px){.code-box{font-size:22px;padding:14px 18px}}
  </style>
</head>
<body>
  <div class="container" role="article" aria-label="Admin verification code">
    <div class="header">
      <h2 style="margin:0;font-size:18px">OLIN — Admin Verification</h2>
    </div>

    <div class="content">
      <p style="margin:0 0 8px 0">Hello {{ $user->name ?? 'Admin' }},</p>

      <p style="margin:0 0 12px 0;color:#374151">Use the verification code below to confirm your action. This code expires in 5 minutes.</p>

      <div class="code-box" aria-hidden="true">{{ $code }}</div>

      <p class="note">If you didn't request this code, please contact the system administrator immediately.</p>

      <p style="margin-top:12px">
        <a href="#" class="btn" aria-hidden="true">Do not share this code</a>
      </p>
    </div>

    <div class="footer">
      <div>OLIN • <span style="opacity:.9">This is an automated message. Please do not reply.</span></div>
    </div>
  </div>
</body>
</html>
