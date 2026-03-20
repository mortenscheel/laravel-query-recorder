<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder;

use Illuminate\Support\ServiceProvider;
use Override;

class QueryRecorderServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->scoped(QueryRecorderService::class);
    }
}
