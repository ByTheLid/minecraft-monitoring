<?php

namespace App\Models;

use App\Core\Model;

class BoostPackage extends Model
{
    protected static string $table = 'boost_packages';

    public static function getActive(): array
    {
        return static::db()->query(
            "SELECT * FROM boost_packages WHERE is_active = 1 ORDER BY price ASC"
        )->fetchAll();
    }
}
