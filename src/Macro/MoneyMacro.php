<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Format money/currency.
 *
 * Usage:
 * - ##money(1234.56)## - €1,234.56
 * - ##money(1234.56, "USD")## - $1,234.56
 * - ##money(1234.56, "GBP")## - £1,234.56
 * - ##money(1234.56, "JPY")## - ¥1,235
 */
final class MoneyMacro implements MacroInterface
{
    private const array CURRENCIES = [
        'EUR' => ['symbol' => '€', 'decimals' => 2, 'position' => 'before'],
        'USD' => ['symbol' => '$', 'decimals' => 2, 'position' => 'before'],
        'GBP' => ['symbol' => '£', 'decimals' => 2, 'position' => 'before'],
        'JPY' => ['symbol' => '¥', 'decimals' => 0, 'position' => 'before'],
        'CHF' => ['symbol' => 'CHF', 'decimals' => 2, 'position' => 'after'],
        'CAD' => ['symbol' => 'CA$', 'decimals' => 2, 'position' => 'before'],
        'AUD' => ['symbol' => 'A$', 'decimals' => 2, 'position' => 'before'],
        'CNY' => ['symbol' => '¥', 'decimals' => 2, 'position' => 'before'],
        'INR' => ['symbol' => '₹', 'decimals' => 2, 'position' => 'before'],
        'BRL' => ['symbol' => 'R$', 'decimals' => 2, 'position' => 'before'],
        'RUB' => ['symbol' => '₽', 'decimals' => 2, 'position' => 'after'],
        'KRW' => ['symbol' => '₩', 'decimals' => 0, 'position' => 'before'],
        'BTC' => ['symbol' => '₿', 'decimals' => 8, 'position' => 'before'],
    ];

    public function __construct(
        private readonly string $defaultCurrency = 'EUR',
    ) {
    }

    public function getName(): string
    {
        return 'money';
    }

    public function execute(array $args): string
    {
        $amount = (float) ($args[0] ?? 0);
        $currency = strtoupper((string) ($args[1] ?? $this->defaultCurrency));

        $config = self::CURRENCIES[$currency] ?? self::CURRENCIES['EUR'];

        $formatted = number_format(
            $amount,
            $config['decimals'],
            ',',
            ' ',
        );

        return $config['position'] === 'before'
            ? $config['symbol'] . $formatted
            : $formatted . ' ' . $config['symbol'];
    }
}
