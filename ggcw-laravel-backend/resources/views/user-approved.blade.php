<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Approved</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:30px 0;">
<tr>
<td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.08);">

<tr>
<td style="background-color:#16a34a; padding:24px 30px;">
<h1 style="margin:0; color:#ffffff; font-size:20px;">GGCW Complaint &amp; Feedback Portal</h1>
</td>
</tr>

<tr>
<td style="padding:30px;">
<h2 style="margin-top:0; color:#111827; font-size:18px;">✅ Your account has been approved!</h2>

<p style="color:#374151; font-size:15px; line-height:1.6;">
Dear <strong>{{ $user->name }}</strong>,
</p>

<p style="color:#374151; font-size:15px; line-height:1.6;">
Your registration on the GGCW Complaint &amp; Feedback Portal has been <strong>approved</strong> by the administrator.
You can now log in to your <strong>{{ ucfirst($user->role) }}</strong> account.
</p>

@if($user->roll_no)
<p style="color:#374151; font-size:15px; line-height:1.6;">
<strong>Roll No:</strong> {{ $user->roll_no }}<br>
@if($user->department)
<strong>Department:</strong> {{ $user->department }}
@endif
</p>
@endif

<div style="text-align:center; margin:28px 0;">
<a href="{{ rtrim(env('FRONTEND_URL', 'https://ggcwcomplaints.com'), '/') }}/"
   style="background-color:#16a34a; color:#ffffff; text-decoration:none; padding:12px 28px; border-radius:6px; font-size:15px; display:inline-block;">
   Log In Now
</a>
</div>

<p style="color:#6b7280; font-size:13px; line-height:1.6;">
If you did not create this account, please contact your institution's administration office.
</p>
</td>
</tr>

<tr>
<td style="padding:18px 30px; background-color:#f9fafb; border-top:1px solid #e5e7eb;">
<p style="margin:0; color:#9ca3af; font-size:12px; text-align:center;">
&copy; {{ date('Y') }} GGCW Complaint &amp; Feedback Portal. This is an automated email, please do not reply.
</p>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>