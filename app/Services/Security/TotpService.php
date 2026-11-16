<?php

namespace App\Services\Security;

use App\Models\User;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Hasilkan secret key Base32 sepanjang 16-32 karakter.
     */
    public function generateSecretKey(int $length = 16): string
    {
        $secret = '';
        $alphabetLength = strlen(self::BASE32_ALPHABET);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $secret;
    }

    /**
     * Hasilkan URI OTP Auth untuk aplikasi pemindai TOTP (Google Authenticator / Aegis).
     */
    public function getOtpAuthUri(User $user, string $secret, string $issuer = 'SIMSE SDAM'): string
    {
        $encodedIssuer = rawurlencode($issuer);
        $encodedAccount = rawurlencode("{$issuer}:{$user->email}");

        return "otpauth://totp/{$encodedAccount}?secret={$secret}&issuer={$encodedIssuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Hasilkan Data URL QR Code SVG untuk ditampilkan di peramban.
     */
    public function getQrCodeSvgDataUrl(string $otpAuthUri): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_SVG,
            'eccLevel' => QRCode::ECC_L,
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($otpAuthUri);
    }

    /**
     * Dapatkan kode 6-digit TOTP untuk waktu saat ini.
     */
    public function getCurrentCode(string $secret): ?string
    {
        $secretBinary = $this->base32Decode($secret);
        if ($secretBinary === null) {
            return null;
        }

        $currentTimeSlice = (int) floor(time() / 30);

        return $this->calculateTotp($secretBinary, $currentTimeSlice);
    }

    /**
     * Verifikasi kode 6 digit TOTP berdasarkan RFC 6238 dengan toleransi window.
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || ! ctype_digit($code)) {
            return false;
        }

        $secretBinary = $this->base32Decode($secret);
        if ($secretBinary === null) {
            return false;
        }

        $currentTimeSlice = (int) floor(time() / 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            $calculatedCode = $this->calculateTotp($secretBinary, $currentTimeSlice + $offset);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hasilkan kode pemulihan darurat (Recovery Codes).
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4).'-'.Str::random(4));
        }

        return $codes;
    }

    /**
     * Hitung 6-digit TOTP dari time slice.
     */
    private function calculateTotp(string $secretBinary, int $timeSlice): string
    {
        $binaryTime = pack('N*', 0).pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $binaryTime, $secretBinary, true);

        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $code = $truncatedHash % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Dekode string Base32 menjadi raw binary.
     */
    private function base32Decode(string $base32): ?string
    {
        $base32 = strtoupper(str_replace('=', '', $base32));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0; $i < strlen($base32); $i++) {
            $char = $base32[$i];
            $val = strpos(self::BASE32_ALPHABET, $char);
            if ($val === false) {
                return null;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
