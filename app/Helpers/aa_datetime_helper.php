<?php

if (! function_exists('aa_datetime_wib')) {
    function aa_datetime_wib($value): ?DateTimeImmutable
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '-' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        $storageTimezone = new DateTimeZone('UTC');
        $displayTimezone = new DateTimeZone('Asia/Jakarta');

        try {
            if (preg_match('/(?:Z|[+-][0-9]{2}:?[0-9]{2})$/', $value) === 1) {
                return (new DateTimeImmutable($value))->setTimezone($displayTimezone);
            }

            foreach (['!Y-m-d H:i:s', '!Y-m-d H:i', '!Y-m-d\TH:i:s', '!Y-m-d'] as $format) {
                $dateTime = DateTimeImmutable::createFromFormat($format, $value, $storageTimezone);
                $errors = DateTimeImmutable::getLastErrors();
                $isValid = $errors === false || (((int) ($errors['warning_count'] ?? 0)) === 0 && ((int) ($errors['error_count'] ?? 0)) === 0);

                if ($dateTime instanceof DateTimeImmutable && $isValid) {
                    return $dateTime->setTimezone($displayTimezone);
                }
            }

            return (new DateTimeImmutable($value, $storageTimezone))->setTimezone($displayTimezone);
        } catch (Throwable) {
            return null;
        }
    }
}

if (! function_exists('aa_format_wib_datetime')) {
    function aa_format_wib_datetime($value, string $format = 'd/m/Y H:i', string $empty = '-'): string
    {
        $dateTime = aa_datetime_wib($value);

        return $dateTime instanceof DateTimeImmutable ? $dateTime->format($format) . ' WIB' : $empty;
    }
}

if (! function_exists('aa_format_wib_date')) {
    function aa_format_wib_date($value, string $format = 'd/m/Y', string $empty = '-'): string
    {
        $dateTime = aa_datetime_wib($value);

        return $dateTime instanceof DateTimeImmutable ? $dateTime->format($format) : $empty;
    }
}

if (! function_exists('aa_format_wib_time')) {
    function aa_format_wib_time($value, string $format = 'H:i', string $empty = ''): string
    {
        $dateTime = aa_datetime_wib($value);

        return $dateTime instanceof DateTimeImmutable ? $dateTime->format($format) : $empty;
    }
}

if (! function_exists('aa_wib_timestamp')) {
    function aa_wib_timestamp($value): int
    {
        $dateTime = aa_datetime_wib($value);

        return $dateTime instanceof DateTimeImmutable ? $dateTime->getTimestamp() : 0;
    }
}
