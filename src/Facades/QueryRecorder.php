<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Facades;

use Illuminate\Support\Facades\Facade;
use Scheel\QueryRecorder\QueryRecorderService;

class QueryRecorder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QueryRecorderService::class;
    }
}
