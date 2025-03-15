<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Scheel\QueryRecorder\Recorders\RecordsQueries;
use Scheel\Tracer\StackTrace;

use function Illuminate\Support\defer;

class QueryRecorderService
{
    /** @param  callable(RecordedQuery):void  $callback */
    public function listen(callable $callback): void
    {
        DB::listen(function (QueryExecuted $event) use ($callback): void {
            if ($frame = StackTrace::getTrace()->ignoreVendor()->ignoreFile(__FILE__)->first()) {
                $record = new RecordedQuery($event->toRawSql(), $event->time, $frame);
                $callback($record);
            }
        });
    }

    public function record(RecordsQueries $recorder): void
    {
        $records = [];
        DB::listen(function (QueryExecuted $event) use (&$records): void {
            if ($frame = StackTrace::getTrace()->ignoreVendor()->ignoreFile(__FILE__)->first()) {
                $records[] = new RecordedQuery($event->toRawSql(), $event->time, $frame);
            }
        });
        defer(function () use ($recorder, &$records): void {
            $recorder->recordQueries(QueryCollection::make($records));
        });
    }
}
