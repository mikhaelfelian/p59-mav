<?php

if (!function_exists('format_angka_rp')) {
    /**
     * Format number to Indonesian Rupiah
     * 
     * @param mixed $angka Number to format
     * @param bool $withRp Include 'Rp ' prefix
     * @return string
     */
    function format_angka_rp($angka, bool $withRp = true)
    {
        if ($angka === null || $angka === '') {
            return $withRp ? 'Rp 0' : '0';
        }
        
        $formatted = number_format($angka, 0, ',', '.');
        return $withRp ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('format_angka_db')) {
    /**
     * Normalize localized numeric string to a float usable for DB writes.
     * Supports:
     * - "1,234,567.89" (US)
     * - "1.234.567,89" (ID/EU)
     * - "1234567,89" or "1234567.89"
     * - Also strips any non-numeric symbols (e.g., Rp, spaces)
     */
    function format_angka_db($str)
    {
        if ($str === null) {
            return 0;
        }
        $str = trim((string) $str);
        if ($str === '') {
            return 0;
        }

        // Keep only digits, separators and minus
        $str = preg_replace('/[^0-9.,-]/', '', $str);

        $hasComma = strpos($str, ',') !== false;
        $hasDot   = strpos($str, '.') !== false;

        if ($hasComma && $hasDot) {
            // Both present: the rightmost separator is the decimal
            $lastDot   = strrpos($str, '.');
            $lastComma = strrpos($str, ',');
            if ($lastComma > $lastDot) {
                // decimal = comma, thousands = dot
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // decimal = dot, thousands = comma
                $str = str_replace(',', '', $str);
                // dot stays as decimal
            }
        } elseif ($hasComma) {
            // Only commas present. Heuristic: if <= 2 digits after last comma -> decimal
            $lastComma = strrpos($str, ',');
            $decLen = strlen($str) - $lastComma - 1;
            if ($decLen > 0 && $decLen <= 2) {
                $str = str_replace(',', '.', $str);
            } else {
                // treat as thousands
                $str = str_replace(',', '', $str);
            }
        } elseif ($hasDot) {
            // Only dots present. Heuristic similar to above
            $lastDot = strrpos($str, '.');
            $decLen = strlen($str) - $lastDot - 1;
            if (!($decLen > 0 && $decLen <= 2)) {
                // treat as thousands
                $str = str_replace('.', '', $str);
            }
        }

        return (float) $str;
    }
}

if (!function_exists('format_angka')) {
    /**
     * Format number with thousand separator
     * 
     * @param mixed $angka Number to format
     * @param int $decimal Number of decimal places
     * @return string
     */
    function format_angka($angka, int $decimal = 0)
    {
        if ($angka === null || $angka === '') {
            return '0';
        }
        
        return number_format($angka, $decimal, ',', '.');
    }
}

if (!function_exists('terbilang')) {
    /**
     * Convert number to Indonesian words
     * 
     * @param mixed $angka Number to convert
     * @return string
     */
    function terbilang($angka)
    {
        $angka = abs($angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $terbilang = '';
        
        if ($angka < 12) {
            $terbilang = ' ' . $baca[$angka];
        } elseif ($angka < 20) {
            $terbilang = terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $terbilang = terbilang($angka / 10) . ' Puluh' . terbilang($angka % 10);
        } elseif ($angka < 200) {
            $terbilang = ' Seratus' . terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $terbilang = terbilang($angka / 100) . ' Ratus' . terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $terbilang = ' Seribu' . terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $terbilang = terbilang($angka / 1000) . ' Ribu' . terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $terbilang = terbilang($angka / 1000000) . ' Juta' . terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $terbilang = terbilang($angka / 1000000000) . ' Milyar' . terbilang($angka % 1000000000);
        }
        
        return $terbilang;
    }
} 

if (!function_exists('format_nomor')) {
    /**
     * Format number with leading zeros
     * 
     * @param int $number_length Desired length of the formatted number
     * @param int $number Number to format
     * @return string Formatted number with leading zeros
     */
    function format_nomor($number_length, $number)
    {
        return str_pad($number, $number_length, '0', STR_PAD_LEFT);
    }
}