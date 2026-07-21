<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Job Assigned</title>
</head>
<body style="margin:0;padding:0;background:#f0f9ff;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9ff;padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0891b2,#1e3a5f);padding:28px 32px;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;">New Job Assigned</h1>
                            <p style="margin:8px 0 0;color:#cffafe;font-size:14px;">{{ $booking->booking_number }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;color:#0f172a;font-size:16px;">Hello <strong>{{ $provider->name }}</strong>,</p>
                            <p style="margin:0 0 24px;color:#475569;font-size:15px;line-height:1.6;">
                                A new water tank cleaning job has been assigned to you. Open the AR Provider app to view details and accept the job.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="margin:0 0 8px;color:#0f172a;font-size:15px;"><strong>Customer:</strong> {{ $booking->customer_name }}</p>
                                        <p style="margin:0 0 8px;color:#0f172a;font-size:15px;"><strong>Phone:</strong> {{ $booking->customer_phone }}</p>
                                        <p style="margin:0 0 8px;color:#0f172a;font-size:15px;"><strong>Service:</strong> {{ $booking->service?->name ?? 'Water Tank Cleaning' }}</p>
                                        <p style="margin:0 0 8px;color:#0f172a;font-size:15px;"><strong>Date:</strong> {{ $booking->scheduled_date?->format('d M Y') }}</p>
                                        <p style="margin:0 0 8px;color:#0f172a;font-size:15px;"><strong>Amount:</strong> ₹{{ number_format($booking->amount) }}</p>
                                        <p style="margin:0;color:#0f172a;font-size:15px;"><strong>Address:</strong> {{ $booking->customer_address }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
