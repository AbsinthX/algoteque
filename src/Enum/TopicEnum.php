<?php declare(strict_types=1);

namespace App\Enum;

/**
 * Enum with all possible topics
 */
enum TopicEnum: string
{
    case MATH = 'math';
    case READING = 'reading';
    case SCIENCE = 'science';
    case HISTORY = 'history';
    case ART = 'art';

    /**
     * Get all Enum values
     *
     * @return array
     */
    public static function getValues(): array
    {
        return array_map(fn(self $topic) => $topic->value, self::cases());
    }

    /**
     * Checks if a given value is a valid enum value.
     *
     * @param string $value
     * @return bool
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::getValues(), true);
    }
}
