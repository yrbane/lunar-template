<?php

declare(strict_types=1);

namespace Lunar\Template\Security;

/**
 * HTML escaper for safe template output.
 */
class HtmlEscaper implements EscaperInterface
{
    /**
     * @param string $charset Character encoding for escaping
     */
    public function __construct(
        private readonly string $charset = 'UTF-8',
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function escape(mixed $value): string
    {
        $string = $this->convertToString($value);

        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $this->charset);
    }

    /**
     * {@inheritDoc}
     */
    public function getStrategy(): string
    {
        return 'html';
    }

    /**
     * Convert a value to string.
     */
    private function convertToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (\is_bool($value)) {
            return $value ? '1' : '';
        }

        if (\is_array($value)) {
            return 'Array';
        }

        if (\is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            return 'Object';
        }

        return (string) $value;
    }
}
