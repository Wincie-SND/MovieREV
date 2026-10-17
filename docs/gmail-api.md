# Gmail API mail setup

MovieREV sends Laravel mail through the Gmail API when `MAIL_MAILER=gmail-api`. This uses Laravel's existing notification and mail system, so email-verification links and account security-code emails go through the same Gmail sender.

## Google credentials

1. Create a Google Cloud project and enable the Gmail API.
2. Configure the OAuth consent screen and add the Gmail account that will send MovieREV mail as a test user while developing.
3. Create a Web application OAuth client. For the OAuth Playground setup below, add `https://developers.google.com/oauthplayground` as an authorized redirect URI.
4. Open the [Google OAuth 2.0 Playground](https://developers.google.com/oauthplayground), open its settings, choose **Use your own OAuth credentials**, and enter the client ID and secret.
5. Request the scope `https://www.googleapis.com/auth/gmail.send`, authorize using the MovieREV sender account, and exchange the authorization code for tokens. Copy the refresh token.
6. Add these settings to the local `.env` file. Keep the client secret and refresh token private; never commit them.

```dotenv
MAIL_MAILER=gmail-api
MAIL_FROM_ADDRESS=your-movierev-sender@gmail.com
MAIL_FROM_NAME=MovieREV
GMAIL_API_CLIENT_ID=your-client-id
GMAIL_API_CLIENT_SECRET=your-client-secret
GMAIL_API_REFRESH_TOKEN=your-refresh-token
```

`MAIL_FROM_ADDRESS` must be the authorized Gmail account or one of its configured send-as aliases. After changing `.env`, clear Laravel's cached configuration with `php artisan config:clear`. In Docker, recreate the `app` container if it reads the values from Compose's `env_file`.

## Important Google OAuth testing limit

The `gmail.send` scope is sensitive. An external OAuth app left in **Testing** has refresh tokens that expire after seven days, so the sender account must authorize again when that happens. Google documents this behavior in its [OAuth refresh-token guidance](https://developers.google.com/identity/protocols/oauth2#expiration). Publishing the app or using it beyond a private school demo may require Google's OAuth verification; see [Gmail API scopes](https://developers.google.com/workspace/gmail/api/auth/scopes).

## MovieREV account codes

- Laravel sends its normal signed verification link after registration and when the user requests another verification email.
- Password changes send a six-digit code to the account's current email. The user must enter their current password before requesting the code.
- Email changes send the code to the proposed new address. MovieREV changes and marks that address verified only after the code is confirmed.
- Codes are stored hashed in Laravel's configured cache, expire after ten minutes, and are invalidated after five incorrect attempts. Code-send routes are rate limited.
- Verification links use `APP_URL`; set it to a URL the user can open when the app is deployed. A `localhost` link only works from the machine running the app.
