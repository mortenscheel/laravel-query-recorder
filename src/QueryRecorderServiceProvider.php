<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder;

use Illuminate\Support\ServiceProvider;

class QueryRecorderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(QueryRecorderService::class);
    }
}
