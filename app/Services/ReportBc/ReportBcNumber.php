<?php

namespace App\Services\ReportBc;

class ReportBcNumber
{
    public static function format($value): string
    {
        return number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
