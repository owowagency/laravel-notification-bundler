<?php

namespace Owowagency\NotificationBundler\Tests\Support\Middleware;

use Illuminate\Contracts\Queue\ShouldQueue;

class StopExecution
{
    /**
     * Process the queued job.
     */
    public function handle(ShouldQueue $job, \Closure $next)
    {
        return false;
    }
}
