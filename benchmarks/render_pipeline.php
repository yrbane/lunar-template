<?php

declare(strict_types=1);

/**
 * Benchmark : pipeline de rendu (compilation + cache + render).
 *
 * Usage : php benchmarks/render_pipeline.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Lunar\Template\AdvancedTemplateEngine;

const RENDERS = 10_000;

$tplDir = sys_get_temp_dir() . '/bench-tpl-' . uniqid();
$cacheDir = sys_get_temp_dir() . '/bench-cache-' . uniqid();
mkdir($tplDir);
mkdir($cacheDir);

$template = <<<'TPL'
<!DOCTYPE html>
<html>
<head><title>[[ title ]]</title></head>
<body>
  <h1>[[ user.name ]]</h1>
  [% if items %]
    <ul>
    [% for item in items %]
      <li>[[ item.label ]] — [[ item.price ]]</li>
    [% endfor %]
    </ul>
  [% endif %]
  <p>Total : [[ total ]]</p>
</body>
</html>
TPL;
file_put_contents("$tplDir/page.tpl", $template);

$data = [
    'title' => 'Catalogue',
    'user' => ['name' => 'Jean Dupont'],
    'items' => [
        ['label' => 'Livre', 'price' => '15€'],
        ['label' => 'CD', 'price' => '12€'],
        ['label' => 'DVD', 'price' => '20€'],
    ],
    'total' => '47€',
];

$engine = new AdvancedTemplateEngine($tplDir, $cacheDir);

// Warm-up : première compilation
$engine->render('page', $data);

// Mesure : rendus successifs (cache chaud)
$start = hrtime(true);
for ($i = 0; $i < RENDERS; $i++) {
    $engine->render('page', $data);
}
$elapsed = (hrtime(true) - $start) / 1e6; // ms
$perRender = $elapsed * 1000 / RENDERS;    // µs / render

printf("Pipeline complet — %s renders\n", number_format(RENDERS));
printf("Total           : %.2f ms\n", $elapsed);
printf("Par render      : %.2f µs (%.4f ms)\n", $perRender, $perRender / 1000);
printf("Throughput      : %s renders/s\n", number_format(1_000_000 / $perRender));

// Nettoyage
foreach (glob("$cacheDir/*") as $f) {
    unlink($f);
}
foreach (glob("$tplDir/*") as $f) {
    unlink($f);
}
rmdir($cacheDir);
rmdir($tplDir);
