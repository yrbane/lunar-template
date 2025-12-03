<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Debug dump variable.
 *
 * Usage: ##dump(variable)##
 */
final class DumpMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'dump';
    }

    public function execute(array $args): string
    {
        $output = '<pre style="background:#1e1e1e;color:#dcdcdc;padding:1em;border-radius:4px;overflow:auto;font-family:monospace;font-size:13px;">';

        foreach ($args as $index => $value) {
            $output .= '<span style="color:#569cd6;">[$' . $index . ']</span> ';
            $output .= $this->formatValue($value);
            $output .= "\n";
        }

        $output .= '</pre>';

        return $output;
    }

    private function formatValue(mixed $value, int $depth = 0): string
    {
        $indent = str_repeat('  ', $depth);

        if ($value === null) {
            return '<span style="color:#569cd6;">null</span>';
        }

        if (\is_bool($value)) {
            return '<span style="color:#569cd6;">' . ($value ? 'true' : 'false') . '</span>';
        }

        if (\is_int($value) || \is_float($value)) {
            return '<span style="color:#b5cea8;">' . $value . '</span>';
        }

        if (\is_string($value)) {
            return '<span style="color:#ce9178;">"' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"</span>';
        }

        if (\is_array($value)) {
            if ($value === []) {
                return '<span style="color:#dcdcdc;">[]</span>';
            }

            if ($depth > 3) {
                return '<span style="color:#808080;">[...]</span>';
            }

            $output = "<span style=\"color:#dcdcdc;\">[\n</span>";
            foreach ($value as $k => $v) {
                $output .= $indent . '  ';
                $output .= '<span style="color:#9cdcfe;">' . htmlspecialchars((string) $k, ENT_QUOTES, 'UTF-8') . '</span>';
                $output .= ' <span style="color:#dcdcdc;">=></span> ';
                $output .= $this->formatValue($v, $depth + 1);
                $output .= "\n";
            }
            $output .= $indent . '<span style="color:#dcdcdc;">]</span>';

            return $output;
        }

        if (\is_object($value)) {
            return '<span style="color:#4ec9b0;">' . $value::class . '</span>';
        }

        return '<span style="color:#808080;">(' . \gettype($value) . ')</span>';
    }
}
