@extends('emails.layout')

@section('title', 'Verify your MovieREV email')
@section('eyebrow', 'Welcome to MovieREV')
@section('heading', 'Verify your email address')

@section('content')
    <p style="margin:0 0 18px;color:#f5f2f5;">Hi {{ $userName }},</p>
    <p style="margin:0 0 24px;">Thanks for joining MovieREV. Confirm your email address to finish setting up your account.</p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 24px;border-collapse:separate;">
        <tr>
            <td align="center" bgcolor="#f0444f" style="border-radius:999px;">
                <a href="{{ $verificationUrl }}" style="display:inline-block;padding:13px 22px;color:#ffffff;font-size:14px;font-weight:bold;text-decoration:none;">Verify email address</a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 14px;">This link expires in {{ $expiresMinutes }} minutes. If you didn’t create a MovieREV account, you can ignore this email.</p>
    <p style="margin:0 0 8px;color:#aaa1b2;font-size:12px;">If the button doesn’t work, copy this link into your browser:</p>
    <p style="margin:0;overflow-wrap:anywhere;font-size:12px;line-height:1.5;"><a href="{{ $verificationUrl }}" style="color:#fb7185;">{{ $verificationUrl }}</a></p>
@endsection
