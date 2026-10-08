<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

trait GroupsByMonth
{
    protected function monthKeySql(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', $column)"
            : "DATE_FORMAT($column, '%Y-%m')";
    }
}
