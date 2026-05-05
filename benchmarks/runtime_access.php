<?php

declare(strict_types=1);

/**
 * Benchmark : overhead du helper Runtime\Access::get vs accès PHP natif.
 *
 * Usage : php benchmarks/runtime_access.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Lunar\Template\Runtime\Access;

const ITERATIONS = 1_000_000;

$obj = new readonly class('fr', 'ltr') {
    public function __construct(public string $code, public string $direction)
    {
    }
};
$arr = ['code' => 'fr', 'direction' => 'ltr'];

function bench(string $label, callable $fn): void
{
    $start = hrtime(true);
    for ($i = 0; $i < ITERATIONS; $i++) {
        $fn();
    }
    $elapsed = (hrtime(true) - $start) / 1e6; // ms
    $perOp = $elapsed * 1000 / ITERATIONS;     // µs / op
    printf("%-50s %8.2f ms total | %6.3f µs/op\n", $label, $elapsed, $perOp);
}

echo "Runtime\\Access — " . number_format(ITERATIONS) . " itérations chacune\n";
echo str_repeat('-', 80) . "\n";

bench('Access::get(object, prop)', static fn () => Access::get($obj, 'code'));
bench('  natif: $obj->code', static fn () => $obj->code);

bench('Access::get(array, key)', static fn () => Access::get($arr, 'code'));
bench('  natif: $arr[\'code\']', static fn () => $arr['code']);

bench('Access::callMethod(obj, method)', static fn () => Access::callMethod($obj, 'code'));
bench('  natif: $obj->code (équivalent)', static fn () => $obj->code);

echo "\nLecture : Access::get vs natif → ratio µs/op = overhead du helper\n";
echo "(rappel : les templates sont compilés et cachés, l'impact runtime\n";
echo " ne se paye qu'une fois par accès dans la page rendue).\n";
