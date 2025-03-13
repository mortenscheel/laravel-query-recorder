<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Scheel\QueryRecorder\Processor\QueryRecordProcessor;
use Scheel\QueryRecorder\Trace\Trace;

class QueryRecorderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        DatabaseManager::macro('recordQueries', function (QueryRecordProcessor $processor): void {
            $records = [];
            DB::listen(function (QueryExecuted $event) use (&$records): void {
                $frame = Trace::getTrace()->ignoreVendor()->ignoreSelf()->first();
                if ($frame) {
                    $records[] = new RecordedQuery($event->toRawSql(), $event->time, $frame);
                }
            });
            defer(function () use ($processor, &$records): void {
                $processor->process($records);
            });
        });
    }
}
