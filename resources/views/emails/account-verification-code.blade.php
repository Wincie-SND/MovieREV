@extends('emails.layout')

@section('title', $purpose === 'email-change' ? 'Confirm your new MovieREV email' : 'Your MovieREV password change code')
@section('eyebrow', 'MovieREV security')
@section('heading', $purpose === 'email-change' ? 'Confirm your new email' : 'Confirm your password change')

@section('content')
    <p style="margin:0 0 18px;color:#f5f2f5;">Hi {{ $userName }},</p>

    @if ($purpose === 'email-change')
        <p style="margin:0 0 24px;">Enter this code in MovieREV to confirm that you own this email address. Your account email will change only after you confirm the code.</p>
    @else
        <p style="margin:0 0 24px;">Enter this code in MovieREV to confirm your password change.</p>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 22px;border-collapse:separate;">
        <tr>
            <td align="center" style="border:1px solid #49334d;border-radius:12px;background-color:#100812;padding:18px;">
                <span style="color:#ffffff;font-size:32px;font-weight:bold;letter-spacing:9px;">{{ $code }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 14px;">This one-time code expires in {{ $expiresMinutes }} minutes. MovieREV staff will never ask you to share it.</p>
    <p style="margin:0;color:#aaa1b2;font-size:13px;">If you didn’t request this, ignore this email. No changes will be made without the code.</p>
@endsection
