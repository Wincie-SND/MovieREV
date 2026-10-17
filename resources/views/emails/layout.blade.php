<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MovieREV')</title>
</head>
<body style="margin:0;background-color:#100812;color:#f5f2f5;font-family:Arial,Helvetica,sans-serif;padding:32px 14px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;border:1px solid #38233b;border-radius:16px;background-color:#190d1d;border-collapse:separate;">
                    <tr>
                        <td style="padding:30px 32px 26px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 26px;border-collapse:collapse;">
                                <tr>
                                    <td width="62" valign="middle">
                                        <img src="{{ $message->embed(public_path('img/logo.png')) }}" width="56" height="56" alt="MovieREV logo" style="display:block;width:56px;height:56px;border:0;">
                                    </td>
                                    <td valign="middle" style="padding-left:12px;">
                                        <p style="margin:0 0 4px;color:#fb4657;font-size:17px;font-weight:bold;letter-spacing:2px;">MovieREV</p>
                                        <p style="margin:0;color:#a99fad;font-size:10px;letter-spacing:1.5px;text-transform:uppercase;">Movie reviews, together</p>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 10px;color:#fb7185;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">@yield('eyebrow', 'Account security')</p>
                            <h1 style="margin:0 0 18px;color:#ffffff;font-size:26px;line-height:1.25;">@yield('heading')</h1>
                            <div style="color:#d6d0da;font-size:15px;line-height:1.65;">
                                @yield('content')
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #38233b;padding:18px 32px;color:#a99fad;font-size:12px;line-height:1.6;">
                            MovieREV · Reviews from a community of movie fans.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
