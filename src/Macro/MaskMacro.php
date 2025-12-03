<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Mask sensitive data.
 *
 * Usage:
 * - ##mask("1234567890123456")## - ************3456
 * - ##mask("john@example.com", "email")## - j***@example.com
 * - ##mask("555-123-4567", "phone")## - ***-***-4567
 * - ##mask("secret", "full")## - ******
 */
final class MaskMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'mask';
    }

    public function execute(array $args): string
    {
        $value = (string) ($args[0] ?? '');
        $type = (string) ($args[1] ?? 'default');
        $char = (string) ($args[2] ?? '*');
        $keep = (int) ($args[3] ?? 4);

        if ($value === '') {
            return '';
        }

        return match ($type) {
            'email' => $this->maskEmail($value, $char),
            'phone' => $this->maskPhone($value, $char),
            'card', 'credit' => $this->maskCard($value, $char),
            'full' => str_repeat($char, mb_strlen($value)),
            'middle' => $this->maskMiddle($value, $char, $keep),
            default => $this->maskDefault($value, $char, $keep),
        };
    }

    private function maskDefault(string $value, string $char, int $keep): string
    {
        $length = mb_strlen($value);

        if ($length <= $keep) {
            return str_repeat($char, $length);
        }

        return str_repeat($char, $length - $keep) . mb_substr($value, -$keep);
    }

    private function maskEmail(string $email, string $char): string
    {
        $parts = explode('@', $email);

        if (\count($parts) !== 2) {
            return $this->maskDefault($email, $char, 4);
        }

        $local = $parts[0];
        $domain = $parts[1];

        $maskedLocal = mb_strlen($local) > 1
            ? mb_substr($local, 0, 1) . str_repeat($char, mb_strlen($local) - 1)
            : $char;

        return $maskedLocal . '@' . $domain;
    }

    private function maskPhone(string $phone, string $char): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === null || mb_strlen($digits) < 4) {
            return str_repeat($char, mb_strlen($phone));
        }

        $last4 = mb_substr($digits, -4);
        $masked = preg_replace('/\d/', $char, mb_substr($phone, 0, -4));

        return $masked . $last4;
    }

    private function maskCard(string $card, string $char): string
    {
        $digits = preg_replace('/\D/', '', $card);

        if ($digits === null || mb_strlen($digits) < 4) {
            return str_repeat($char, mb_strlen($card));
        }

        $last4 = mb_substr($digits, -4);

        return str_repeat($char, 4) . ' ' . str_repeat($char, 4) . ' ' . str_repeat($char, 4) . ' ' . $last4;
    }

    private function maskMiddle(string $value, string $char, int $keep): string
    {
        $length = mb_strlen($value);

        if ($length <= $keep * 2) {
            return str_repeat($char, $length);
        }

        $start = mb_substr($value, 0, $keep);
        $end = mb_substr($value, -$keep);
        $middle = str_repeat($char, $length - ($keep * 2));

        return $start . $middle . $end;
    }
}
