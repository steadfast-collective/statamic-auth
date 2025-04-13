<?php

namespace SteadfastCollective\StatamicAuth\Traits;

use PragmaRX\Recovery\Recovery;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use Illuminate\Validation\ValidationException;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use PragmaRX\Google2FAQRCode\Google2FA as QrCode;
use PragmaRX\Google2FALaravel\Facade as Google2FAFacade;
use PragmaRX\Google2FALaravel\Google2FA;

trait UsesTwoFactor 
{
    public $google2fa;
    
    public function initializeUsesTwoFactor()
    {
        $this->mergeCasts([
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_recovery_code_last_used'
        ]);

        $this->google2fa = new Google2FA(request());
    }
    
    public function twoFactorEnabled(): Attribute
    {
        return Attribute::make(
            get: function() {
                return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
            }
        );
    }

    public function enableTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_secret' => Google2FAFacade::generateSecretKey(),
            'two_factor_recovery_codes' => $this->createRecoveryCodes()
        ])->save();
    }

    public function createRecoveryCodes(int $count = 8): array
    {
        $recovery = new Recovery();

        return $recovery
            ->setCount($count)
            ->setBlocks(1)
            ->setChars(8)
            ->toCollection()
            ->all(); 
    }

    public function twoFactorQrCodeSvg(): string
    {
        $qrCodeGenerator = new QrCode();

        return $qrCodeGenerator
            ->setQrCodeService(
                new Bacon(
                    new SvgImageBackEnd()
                )
            )
            ->getQRCodeInline(
                config('app.name'),
                $this->email,
                $this->two_factor_secret
            );
    }

    public function disableTwoFactorAuthentication(): void
    {
        $this
            ->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])
            ->save();
    }

    public function verifyOTP(string $code): bool
    {
        return $this->google2fa->verifyGoogle2FA($this->two_factor_secret, $code);
    }

    public function verifyRecoveryCode(string $code): bool
    {
        if(!$this->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = collect($this->two_factor_recovery_codes);

        $validCode = $recoveryCodes->contains(function($recoveryCode) use ($code) {
            return $recoveryCode === $code;
        });

        if($validCode) {
            $updatedCodes = $recoveryCodes->reject(function($recoveryCode) use ($code) {
                return $recoveryCode === $code;
            });

            $this->two_factor_recovery_codes = $updatedCodes->values()->all();
            $this->two_factor_recovery_code_last_used = now();

            $this->save();

            return true;
        }

        return false;
    }
}