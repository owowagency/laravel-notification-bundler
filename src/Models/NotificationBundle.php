<?php

namespace Owowagency\NotificationBundler\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class NotificationBundle extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'uuid',
        'channel',
        'bundle_identifier',
        'payload',
    ];

    protected function unserializedPayload(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => unserialize($attributes['payload']),
        );
    }
}
