<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesUuid
{
    /**
     * Boot function from Laravel.
     */
    protected static function bootGeneratesUuid()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Override the default incrementing key type.
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Override the default key type.
     */
    public function getKeyType()
    {
        return 'string';
    }
}
