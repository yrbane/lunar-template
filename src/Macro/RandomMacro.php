<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate random number or string.
 *
 * Usage:
 * - ##random()## - Random number between 0 and 100
 * - ##random(1, 10)## - Random number between 1 and 10
 * - ##random("string", 16)## - Random string of 16 chars
 * - ##random("hex", 32)## - Random hex string
 * - ##random("alpha", 8)## - Random alphabetic string
 */
final class RandomMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'random';
    }

    public function execute(array $args): string|int
    {
        $first = $args[0] ?? 0;

        // String generation
        if (\is_string($first)) {
            $length = (int) ($args[1] ?? 16);

            $length = max(1, $length);

            return match ($first) {
                'string' => $this->randomString($length),
                'hex' => bin2hex(random_bytes(max(1, (int) ceil($length / 2)))),
                'alpha' => $this->randomAlpha($length),
                'alnum' => $this->randomAlnum($length),
                'token' => $this->randomToken($length),
                default => $this->randomString($length),
            };
        }

        // Number generation
        $min = (int) $first;
        $max = (int) ($args[1] ?? 100);

        return random_int($min, $max);
    }

    private function randomString(int $length): string
    {
        $bytes = max(1, (int) ceil($length / 2));

        return substr(bin2hex(random_bytes($bytes)), 0, $length);
    }

    private function randomAlpha(int $length): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        $max = \strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }

    private function randomAlnum(int $length): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        $max = \strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }

    private function randomToken(int $length): string
    {
        $bytes = max(1, (int) ceil($length * 0.75));

        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }
}
