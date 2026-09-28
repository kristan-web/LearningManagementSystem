{{-- OTP email. Styles are inline (Tailwind palette values) because email clients strip <style> and class-based CSS. --}}
@php($logo = config('services.brevo.logo_url'))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Your verification code</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Instrument Sans',ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:40px 16px;">

                {{-- Logo (above the card) --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:460px;">
                    <tr>
                        <td align="center" style="padding:0 0 24px;">
                            @if ($logo)
                                <img src="{{ $logo }}" alt="LMS" width="96" style="display:block;width:96px;height:auto;border:0;outline:none;">
                            @else
                                {{-- Text wordmark when no public logo URL is configured (images on localhost can't load in inboxes) --}}
                                <span style="font-size:30px;line-height:36px;font-weight:800;letter-spacing:1px;color:#0a5cf5;">&#10022;&nbsp;LMS</span>
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- Card --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:460px;background-color:#ffffff;border-radius:20px;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="padding:40px 36px 36px;">

                            <p style="margin:0;font-size:13px;line-height:20px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#0284c7;text-align:center;">Verification code</p>
                            <h1 style="margin:8px 0 0;font-size:26px;line-height:34px;font-weight:800;color:#0f172a;text-align:center;">Confirm it's you</h1>
                            <p style="margin:12px 0 0;font-size:15px;line-height:24px;color:#64748b;text-align:center;">
                                {{ $intro ?? 'Enter this code to verify your email.' }}<br>It expires in <strong style="color:#0f172a;">10 minutes</strong>.
                            </p>

                            {{-- Code: one cell per digit --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:28px auto 0;">
                                <tr>
                                    @foreach (str_split($code) as $digit)
                                        <td style="padding:0 4px;">
                                            <div style="width:44px;height:54px;line-height:54px;border-radius:12px;background-color:#f0f9ff;border:1px solid #bae6fd;text-align:center;font-size:26px;font-weight:800;color:#075985;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;">{{ $digit }}</div>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>

                            @if ($link ?? null)
                            {{-- Button: full width, bgcolor on the cell so Outlook still paints it --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:32px;">
                                <tr>
                                    <td align="center" bgcolor="#0a5cf5" style="border-radius:12px;background-color:#0a5cf5;background-image:linear-gradient(180deg,#0ea5e9 0%,#0a5cf5 100%);border-bottom:3px solid #0643b8;">
                                        <a href="{{ $link }}" target="_blank" style="display:block;padding:16px 24px;font-size:16px;line-height:20px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:12px;">Verify my email</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:16px 0 0;font-size:12px;line-height:18px;color:#94a3b8;text-align:center;">
                                Button not working? Open this link:<br>
                                <a href="{{ $link }}" target="_blank" style="color:#0284c7;text-decoration:underline;word-break:break-all;">{{ $link }}</a>
                            </p>

                            @endif

                            {{-- Divider + notice --}}
                            <div style="margin:28px 0 0;border-top:1px solid #f1f5f9;"></div>
                            <p style="margin:20px 0 0;font-size:13px;line-height:20px;color:#64748b;text-align:center;">
                                Didn't request this? You can ignore this email.<br>
                                <strong style="color:#475569;">Never share this code with anyone.</strong>
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="margin:24px 0 0;font-size:12px;color:#94a3b8;">&copy; {{ date('Y') }} LMS. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
