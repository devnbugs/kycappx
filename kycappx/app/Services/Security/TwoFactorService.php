<?php

namespace App\Services\Security;

use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(private Google2FA $google2fa)
    {
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, preg_replace('/\s+/', '', $code));
    }

    public function recoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl(
            config('app.name', 'Kycappx'),
            $user->email ?: $user->username,
            $secret,
        );

        $renderer = new ImageRenderer(
            new RendererStyle(220, 8),
            new SvgImageBackEnd(),
        );

        return (new Writer($renderer))->writeString($url);
    }
}
