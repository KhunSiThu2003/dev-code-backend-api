<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEV Code - Reset Password</title>
</head>
<body style="margin:0; padding:0; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="padding:24px 12px;">

                <!-- Container -->
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

                    <!-- Content -->
                    <tr>
                        <td style="padding:28px 24px; border:1px solid #e5e7eb; border-radius:6px;">

                            <h2 style="margin:0 0 16px; font-size:22px; font-weight:600; color:#000000;">
                                Reset Your Password
                            </h2>

                            <p style="margin:0 0 20px; font-size:16px; line-height:1.6; color:#374151;">
                                Hello,<br>
                                Use the verification code below to reset your password.
                            </p>

                            <!-- OTP -->
                            <table align="center" cellpadding="0" cellspacing="0" style="margin:24px auto;">
                                <tr>
                                    <td style="padding:16px 28px; border:1px solid #e5e7eb; border-radius:6px; text-align:center;">
                                        <span style="
                                            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
                                            font-size:32px;
                                            font-weight:700;
                                            letter-spacing:6px;
                                            color:#000000;
                                        ">
                                            {{ $otp }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#6b7280; border-top:1px solid #e5e7eb; padding-top:16px;">
                                This code will expire in <strong>10 minutes</strong>.
                                For security reasons, do not share this code with anyone.
                            </p>

                            <p style="margin:0; font-size:15px; color:#000000;">
                                Thank you,<br><br>
                                <strong>DEV Code Team</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:16px 0; font-size:12px; color:#6b7280;">
                            devcode.mm@gmail.com &nbsp; | &nbsp; +959 944074981<br>
                            Developed by <strong>Khun Si Thu</strong>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
