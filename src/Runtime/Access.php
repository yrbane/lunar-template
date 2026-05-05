<?php

declare(strict_types=1);

namespace Lunar\Template\Runtime;

/**
 * Helper d'accès hybride pour la notation pointée des templates.
 *
 * Permet à `[[ obj.prop ]]` de fonctionner aussi bien avec :
 *  - un tableau `['prop' => …]` (accès par clé)
 *  - un objet (DTO, entity, valeur...) avec une propriété `prop`
 *
 * Garantit la rétro-compatibilité de la syntaxe et évite à l'appelant
 * de devoir convertir ses DTO en arrays côté contrôleur.
 */
final class Access
{
    /**
     * Récupère la valeur d'une propriété (objet) ou clé (tableau).
     *
     * Retourne `null` si la cible est un scalaire, est null, ou si la
     * propriété/clé n'existe pas.
     */
    public static function get(mixed $value, string $key): mixed
    {
        if (\is_object($value)) {
            return $value->{$key} ?? null;
        }

        if (\is_array($value)) {
            return $value[$key] ?? null;
        }

        return null;
    }

    /**
     * Appelle une méthode sur un objet de manière null-safe.
     *
     * Si la cible n'est pas un objet, ou si la méthode n'existe pas,
     * retourne `null` plutôt que de lever une `TypeError` PHP. Permet
     * à `[[ obj.method() ]]` de gérer gracieusement `obj === null`
     * en mode non-strict (en mode strict, le check `=== null` final
     * détecte l'absence de retour).
     *
     * @param mixed $value Cible de l'appel
     * @param string $method Nom de la méthode
     * @param mixed ...$args Arguments à passer à la méthode
     */
    public static function callMethod(mixed $value, string $method, mixed ...$args): mixed
    {
        if (\is_object($value) && method_exists($value, $method)) {
            return $value->{$method}(...$args);
        }

        return null;
    }

    /**
     * Indique si la propriété ou la clé existe sur la cible.
     *
     * Renvoie `true` même si la valeur stockée est `null` (clé/propriété
     * déclarée mais nulle) — distinction utile pour le mode strict.
     */
    public static function has(mixed $value, string $key): bool
    {
        if (\is_object($value)) {
            return isset($value->{$key}) || property_exists($value, $key);
        }

        if (\is_array($value)) {
            return \array_key_exists($key, $value);
        }

        return false;
    }
}
