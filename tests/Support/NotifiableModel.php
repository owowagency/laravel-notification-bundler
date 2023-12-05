<?php

namespace Owowagency\NotificationBundler\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class NotifiableModel extends Model
{
    use Notifiable;

    public $timestamps = false;

    protected $guarded = [];
}
