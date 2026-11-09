<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Time-based one-time password (TOTP) and recovery code handling for administrators.
 */
class TwoFactorAuthenticator
{
    private const int RECOVERY_CODE_COUNT = 8;

    public function __construct(private Google2FA $google2fa) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $otpauthUrl = $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);
        $writer = new Writer(new ImageRenderer(new RendererStyle(200, 1), new SvgImageBackEnd));

        return $writer->writeString($otpauthUrl);
    }

    /**
     * Verify a TOTP code. Each code is accepted only once per user to prevent replay.
     */
    public function verifyCode(string $secret, string $code, int|string $userId): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{6}$/', $code) || ! $this->google2fa->verifyKey($secret, $code)) {
            return false;
        }

        return Cache::add("two-factor-used:{$userId}:{$code}", true, now()->addMinutes(5));
    }

    /**
     * Consume a recovery code. Returns false if it does not match an unused code.
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        $remainingCodes = $user->two_factor_recovery_codes ?? [];
        $matchedIndex = array_search(trim($code), $remainingCodes, true);

        if ($matchedIndex === false) {
            return false;
        }

        unset($remainingCodes[$matchedIndex]);
        $user->forceFill(['two_factor_recovery_codes' => array_values($remainingCodes)])->save();

        return true;
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(): array
    {
        return array_map(
            fn (): string => Str::lower(Str::random(10).'-'.Str::random(10)),
            range(1, self::RECOVERY_CODE_COUNT),
        );
    }
}
