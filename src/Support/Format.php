<?php

declare(strict_types=1);

namespace Rembon\LaravelAuditorFilament\Support;

use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @internal
 */
final class Format
{
    /**
     * Short, readable label for a morph type or class name.
     */
    public static function type(?string $type): string
    {
        if ($type === null || $type === '') {
            return '—';
        }

        return class_basename(Relation::getMorphedModel($type) ?? $type);
    }

    public static function morph(?string $type, ?string $id): string
    {
        return $type === null ? '—' : self::type($type).' #'.$id;
    }

    public static function value(mixed $value): string
    {
        return match (true) {
            $value === null => '—',
            is_bool($value) => $value ? 'true' : 'false',
            is_scalar($value) => (string) $value,
            default => (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        };
    }

    public static function json(mixed $value): string
    {
        return $value === null || $value === [] ? '—' : (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Rows of an old/new diff.
     *
     * @param  array<array-key, mixed>|null  $old
     * @param  array<array-key, mixed>|null  $new
     * @return list<array{attribute: string, old: string, new: string}>
     */
    public static function diff(?array $old, ?array $new): array
    {
        $old ??= [];
        $new ??= [];
        $rows = [];

        foreach (array_unique([...array_keys($old), ...array_keys($new)]) as $attribute) {
            $rows[] = [
                'attribute' => (string) $attribute,
                'old' => array_key_exists($attribute, $old) ? self::value($old[$attribute]) : '—',
                'new' => array_key_exists($attribute, $new) ? self::value($new[$attribute]) : '—',
            ];
        }

        return $rows;
    }
}
