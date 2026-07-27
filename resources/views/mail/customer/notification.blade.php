<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mail->subject }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f6faf7; color:#111827; font-family:Arial, Helvetica, sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        {{ $mail->preheader }}
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f6faf7; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;">
                    <tr>
                        <td style="padding:0 0 20px; font-size:20px; line-height:1; font-weight:700; letter-spacing:-0.02em; color:#102116;">
                            {{ $appName }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #dcebe0; background-color:#ffffff;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="height:5px; background-color:#90d8a8; font-size:0; line-height:0;">&nbsp;</td>
                                </tr>
                            </table>
                            <div style="padding:36px 36px 32px;">
                                <p style="margin:0 0 18px; font-size:11px; line-height:1.4; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#245c37;">
                                    {{ $mail->eyebrow }}
                                </p>

                                <h1 style="margin:0 0 14px; font-size:30px; line-height:1.2; font-weight:700; letter-spacing:-0.02em; color:#102116;">
                                    {{ $mail->title }}
                                </h1>

                                <p style="margin:0 0 22px; font-size:16px; line-height:1.65; color:#374151;">
                                    {{ $mail->intro }}
                                </p>

                                @foreach ($mail->lines as $line)
                                    <p style="margin:0 0 12px; font-size:14px; line-height:1.65; color:#6b7280;">
                                        {{ $line }}
                                    </p>
                                @endforeach

                                @if ($mail->actionText !== null && $mail->actionUrl !== null)
                                    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:28px 0 8px;">
                                        <tr>
                                            <td align="center" style="background-color:#245c37;">
                                                <a href="{{ $mail->actionUrl }}" style="display:inline-block; padding:13px 20px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:700;">
                                                    {{ $mail->actionText }}
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                @endif

                                @if ($mail->facts !== [])
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:28px; border:1px solid #dcebe0; background-color:#f6faf7;">
                                        @foreach ($mail->facts as $label => $value)
                                            <tr>
                                                <td style="padding:12px 14px; width:36%; border-bottom:1px solid #dcebe0; font-size:12px; font-weight:700; color:#245c37;">
                                                    {{ $label }}
                                                </td>
                                                <td style="padding:12px 14px; border-bottom:1px solid #dcebe0; font-size:13px; color:#374151;">
                                                    {{ $value }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif

                                @foreach ($mail->footerLines as $line)
                                    <p style="margin:20px 0 0; font-size:13px; line-height:1.6; color:#6b7280;">
                                        {{ $line }}
                                    </p>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 4px 0; font-size:12px; line-height:1.6; color:#6b7280;">
                            Need help? Reply to this email or contact {{ config('mail.from.address') }}.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 4px 0; font-size:11px; line-height:1.6; color:#9ca3af;">
                            &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
