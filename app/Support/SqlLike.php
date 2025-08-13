<?php

namespace App\Support;

final class SqlLike
{
    public static function escape(string $term): string
    {
        // \, %, _ escapelése LIKE mintában
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term) . '%';
    }
}
