<?php

namespace App\Enums;

/**
 * Hari Enum - Definisi hari untuk jadwal mengajar dalam sistem
 *
 * Enum ini bertujuan untuk menyediakan konstanta hari yang konsisten
 * di seluruh aplikasi, yaitu: senin sampai sabtu untuk jadwal sekolah
 */
enum Hari: string
{
    case SENIN = 'senin';
    case SELASA = 'selasa';
    case RABU = 'rabu';
    case KAMIS = 'kamis';
    case JUMAT = 'jumat';
    case SABTU = 'sabtu';

    /**
     * Get label dalam Bahasa Indonesia untuk display
     *
     * @return string Label yang ditampilkan ke user
     */
    public function label(): string
    {
        return match ($this) {
            self::SENIN => 'Senin',
            self::SELASA => 'Selasa',
            self::RABU => 'Rabu',
            self::KAMIS => 'Kamis',
            self::JUMAT => 'Jumat',
            self::SABTU => 'Sabtu',
        };
    }

    /**
     * Get short label (3 huruf) untuk matrix view
     *
     * @return string Label singkat
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::SENIN => 'Sen',
            self::SELASA => 'Sel',
            self::RABU => 'Rab',
            self::KAMIS => 'Kam',
            self::JUMAT => 'Jum',
            self::SABTU => 'Sab',
        };
    }

    /**
     * Get urutan hari (1-6) untuk sorting
     *
     * @return int Urutan hari dalam seminggu
     */
    public function order(): int
    {
        return match ($this) {
            self::SENIN => 1,
            self::SELASA => 2,
            self::RABU => 3,
            self::KAMIS => 4,
            self::JUMAT => 5,
            self::SABTU => 6,
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
            self::SENIN => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            self::SELASA => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::RABU => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::KAMIS => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            self::JUMAT => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
            self::SABTU => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
        };
    }

    /**
     * Get array of all day values
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
            fn (self $hari) => [
                'value' => $hari->value,
                'label' => $hari->label(),
            ],
            self::cases()
        );
    }

    /**
     * Get weekdays only (senin-jumat) untuk filter
     *
     * @return array<self>
     */
    public static function weekdays(): array
    {
        return [
            self::SENIN,
            self::SELASA,
            self::RABU,
            self::KAMIS,
            self::JUMAT,
        ];
    }
}
