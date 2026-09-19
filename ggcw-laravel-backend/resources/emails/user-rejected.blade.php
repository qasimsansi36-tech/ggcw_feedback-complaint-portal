<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Update</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:30px 0;">
<tr>
<td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.08);">

<tr>
<td style="background-color:#dc2626; padding:24px 30px;">
<h1 style="margin:0; color:#ffffff; font-size:20px;">GGCW Complaint &amp; Feedback Portal</h1>
</td>
</tr>

<tr>
<td style="padding:30px;">
<h2 style="margin-top:0; color:#111827; font-size:18px;">Aap ki registration request par update</h2>

<p style="color:#374151; font-size:15px; line-height:1.6;">
Assalam-o-Alaikum <strong>{{ $user->name }}</strong>,
</p>

<p style="color:#374151; font-size:15px; line-height:1.6;">
Humein afsos hai ke aap ki GGCW Complaint &amp; Feedback Portal par registration request is waqt admin ne <strong>manzoor nahi</strong> ki hai.
</p>

@if(!empty($reason))
<div style="background-color:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:14px 16px; margin:18px 0;">
<p style="margin:0; color:#991b1b; font-size:14px;"><strong>Wajah:</strong> {{ $reason }}</p>
</div>
@endif

<p style="color:#374151; font-size:15px; line-height:1.6;">
Agar aap ko lagta hai ye galti se hua hai, ya aap dobara apply karna chahte hain, to baraye meherbani apni institution ke administration office se rabta karein.
</p>
</td>
</tr>

<tr>
<td style="padding:18px 30px; background-color:#f9fafb; border-top:1px solid #e5e7eb;">
<p style="margin:0; color:#9ca3af; font-size:12px; text-align:center;">
&copy; {{ date('Y') }} GGCW Complaint &amp; Feedback Portal. Ye ek automated email hai, isay reply na karein.
</p>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>