<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Provider Login Credentials</title>
</head>
<body style="margin:0;padding:0;background:#f0f9ff;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9ff;padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0891b2,#1e3a5f);padding:28px 32px;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;">AR Water Tank Cleaners</h1>
                            <p style="margin:8px 0 0;color:#cffafe;font-size:14px;">Service Provider App</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;color:#0f172a;font-size:16px;">Hello <strong>{{ $provider->name }}</strong>,</p>
                            <p style="margin:0 0 24px;color:#475569;font-size:15px;line-height:1.6;">
                                @if($isReset)
                                    Your provider app password has been reset by the admin. Use the credentials below to log in.
                                @else
                                    Your service provider account has been created. Use these credentials to log in to the AR Provider mobile app.
                                @endif
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="margin:0 0 12px;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Login Details</p>
                                        <p style="margin:0 0 8px;color:#0f172a;font-size:15px;"><strong>Email:</strong> {{ $provider->email }}</p>
                                        <p style="margin:0 0 8px;color:#0f172a;font-size:15px;"><strong>Password:</strong> {{ $plainPassword }}</p>
                                        <p style="margin:0;color:#0f172a;font-size:15px;"><strong>Phone:</strong> {{ $provider->phone }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;color:#475569;font-size:14px;line-height:1.6;">
                                Download the <strong>AR Provider</strong> app and sign in with your email and password.
                            </p>
                            <p style="margin:0;color:#94a3b8;font-size:13px;">
                                Please change your password after first login if the option is available. Do not share these credentials with anyone.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;color:#94a3b8;font-size:12px;text-align:center;">
                                © {{ date('Y') }} AR Water Tank Cleaners · Delhi NCR
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
