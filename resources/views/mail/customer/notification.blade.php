<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mail->subject }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4efe8; color:#1f2937; font-family:Arial, Helvetica, sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        {{ $mail->preheader }}
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4efe8; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;">
                    <tr>
                        <td style="padding-bottom:16px; font-size:12px; letter-spacing:0.18em; text-transform:uppercase; color:#9a3412; font-weight:700;">
                            {{ $appName }}
                        </td>
                    </tr>
                    <tr>
                        <td style="background:linear-gradient(135deg, #fffaf4 0%, #ffffff 100%); border-radius:24px; padding:32px; border:1px solid #f1dfd1; box-shadow:0 18px 40px rgba(15, 23, 42, 0.08);">
                            <div style="display:inline-block; padding:8px 12px; border-radius:999px; background-color:#fff1e8; color:#c2410c; font-size:12px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase;">
                                {{ $mail->eyebrow }}
                            </div>

                            <h1 style="margin:20px 0 12px; font-size:32px; line-height:1.15; color:#111827;">
                                {{ $mail->title }}
                            </h1>

                            <p style="margin:0 0 20px; font-size:16px; line-height:1.7; color:#374151;">
                                {{ $mail->intro }}
                            </p>

                            @foreach ($mail->lines as $line)
                                <p style="margin:0 0 14px; font-size:15px; line-height:1.7; color:#4b5563;">
                                    {{ $line }}
                                </p>
                            @endforeach

                            @if ($mail->actionText !== null && $mail->actionUrl !== null)
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin:28px 0 16px;">
                                    <tr>
                                        <td align="center" style="border-radius:14px; background-color:#111827;">
                                            <a href="{{ $mail->actionUrl }}" style="display:inline-block; padding:14px 22px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700;">
                                                {{ $mail->actionText }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if ($mail->facts !== [])
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:28px; border-collapse:separate; border-spacing:0; background-color:#fff7ed; border:1px solid #fed7aa; border-radius:18px; overflow:hidden;">
                                    @foreach ($mail->facts as $label => $value)
                                        <tr>
                                            <td style="padding:14px 18px; width:36%; border-bottom:1px solid #fed7aa; font-size:13px; font-weight:700; color:#9a3412;">
                                                {{ $label }}
                                            </td>
                                            <td style="padding:14px 18px; border-bottom:1px solid #fed7aa; font-size:14px; color:#431407;">
                                                {{ $value }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @foreach ($mail->footerLines as $line)
                                <p style="margin:18px 0 0; font-size:13px; line-height:1.6; color:#6b7280;">
                                    {{ $line }}
                                </p>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 8px 0; font-size:12px; line-height:1.7; color:#6b7280;">
                            If you need help, reply to this email or contact {{ config('mail.from.address') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
