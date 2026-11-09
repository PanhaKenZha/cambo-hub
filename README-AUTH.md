# Cambo Hub authentication

Laravel application with registration, login, remember me, logout, email verification, password recovery, a protected dashboard, and account settings. Uses SQLite and server-rendered Blade views. No Node build is required.

## Run locally

From this folder:

```powershell
php artisan serve
```

Open http://127.0.0.1:8000/register and create an account.

The dashboard requires email verification. Locally, `MAIL_MAILER=log` writes verification and password reset emails to `storage/logs/laravel.log`. Open the latest complete link from that file in the same browser you registered in. Links expire after 60 minutes. Keep the hostname consistent with `APP_URL` in `.env` (currently `http://localhost:8000`) for signed verification links.

Use `/forgot-password` for password recovery and `/account` to edit your profile, change your password, or delete your account. Changing an email address requires your current password and verification of the new email. Account deletion requires your password and is permanent.

To deliver real emails, configure `MAIL_MAILER=smtp` and your provider's `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, and `MAIL_FROM_ADDRESS` in `.env`, then run `php artisan config:clear`. No SMTP credentials are included.

## Set up a fresh checkout

Requires PHP 8.3+ with PDO SQLite and Composer.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File -Force
php artisan migrate
php artisan serve
```

## Verify

```powershell
php artisan test
```

Routes live in `routes/web.php`, authentication logic in `app/Http/Controllers/AuthController.php`, templates in `resources/views`, and styles in `public/css/app.css`.

Passwords are hashed by the User model. Forms use CSRF protection; authentication regenerates the session; logout invalidates it. Sensitive endpoints have rate limits. Password reset tokens expire and can only be used once. Password changes invalidate other authenticated sessions on their next request.
