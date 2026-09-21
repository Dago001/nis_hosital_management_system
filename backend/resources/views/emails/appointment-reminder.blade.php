<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <div style="max-width:560px;margin:0 auto;padding:24px;">
        <div style="background:#065f46;color:#fff;padding:16px 20px;border-radius:12px 12px 0 0;">
            <div style="font-size:15px;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;">Nigeria Immigration Service</div>
            <div style="font-size:12px;opacity:.9;">Medical Services — Appointment Reminder</div>
        </div>
        <div style="background:#ffffff;padding:24px 20px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 12px 12px;">
            <p style="margin:0 0 12px;">Dear {{ $patientName ?? 'Patient' }},</p>
            <p style="margin:0 0 16px;">This is a reminder of your upcoming appointment at NIS Medical Services:</p>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr><td style="padding:6px 0;color:#64748b;width:120px;">Date</td><td style="padding:6px 0;font-weight:bold;">{{ $date }}</td></tr>
                <tr><td style="padding:6px 0;color:#64748b;">Time</td><td style="padding:6px 0;font-weight:bold;">{{ $time }}</td></tr>
                @if($department)<tr><td style="padding:6px 0;color:#64748b;">Department</td><td style="padding:6px 0;">{{ $department }}</td></tr>@endif
                @if($doctor)<tr><td style="padding:6px 0;color:#64748b;">Doctor</td><td style="padding:6px 0;">Dr. {{ $doctor }}</td></tr>@endif
            </table>
            <p style="margin:16px 0 0;font-size:13px;color:#475569;">Please arrive 15 minutes early with your hospital ID card. If you need to reschedule, contact your NIS medical facility.</p>
        </div>
        <p style="text-align:center;color:#94a3b8;font-size:11px;margin:16px 0 0;">This is an automated reminder. Please do not reply to this email.</p>
    </div>
</body>
</html>
