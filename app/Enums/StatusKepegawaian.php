<?php

namespace App\Enums;

/**
 * StatusKepegawaian Enum - Definisi status kepegawaian guru dalam sistem
 *
 * Enum ini bertujuan untuk menyediakan konstanta status kepegawaian yang konsisten
 * di seluruh aplikasi, yaitu: tetap, honorer, dan kontrak
 */
enum StatusKepegawaian: string
{
    case TETAP = 'tetap';
    case HONORER = 'honorer';
    case KONTRAK = 'kontrak';

    /**
     * Get label dalam Bahasa Indonesia untuk display
     *
     * @return string Label yang ditampilkan ke user
     */
    public function label(): string
    {
        return match ($this) {
            self::TETAP => 'Guru Tetap',
            self::HONORER => 'Guru Honorer',
            self::KONTRAK => 'Guru Kontrak',
        };
    }

    /**
     * Get badge color class untuk Tailwind CSS
     *
     * @return string Tailwind CSS class untuk badge
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::TETAP => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::HONORER => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::KONTRAK => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        };
    }

    /**
     * Get array of all status values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get array untuk dropdown options dengan value dan label
     *
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases()
        );
    }
}
