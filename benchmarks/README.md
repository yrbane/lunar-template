# Benchmarks Lunar Template

Scripts de mesure autonomes (sans framework de bench) pour qualifier l'overhead des helpers introduits en v1.5.0.

## Exécution

```bash
php benchmarks/runtime_access.php   # Helpers Runtime\Access
php benchmarks/render_pipeline.php  # Pipeline render complet (cache chaud)
```

## Résultats de référence (PHP 8.5, x86_64)

### `Runtime\Access` — 1 000 000 itérations

| Opération | Temps total | µs / op | Overhead vs natif |
|---|---:|---:|---:|
| `Access::get(object, prop)` | ~480 ms | 0.48 | **+0.21 µs** |
| natif `$obj->code` | ~267 ms | 0.27 | — |
| `Access::get(array, key)` | ~480 ms | 0.48 | **+0.19 µs** |
| natif `$arr['code']` | ~292 ms | 0.29 | — |
| `Access::callMethod(obj, m)` | ~562 ms | 0.56 | **+0.30 µs** |

### Pipeline complet (cache chaud)

Template avec layout, condition `[% if %]`, boucle `[% for %]`, 6 variables (objet + array imbriqué) :

| Métrique | Valeur |
|---|---:|
| Par render | ~65 µs |
| Throughput | ~15 000 renders/s |

## Lecture

L'overhead du helper `Access::get` est de l'ordre de **0.2 µs par accès** : sur une page typique de 30 expressions, l'impact total reste inférieur à **10 µs**, négligeable comparé au coût d'un appel I/O ou DB.

Les templates étant compilés et mis en cache, l'overhead ne se paye qu'à l'exécution du PHP compilé — la compilation initiale (parsing + injection source-map + injection markers) ne se produit qu'une fois.
