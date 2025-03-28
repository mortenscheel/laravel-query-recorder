<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Scheel\QueryRecorder\Processors\QueryCollectionProcessor;
use Scheel\Tracer\StackTrace;

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

    public function record(QueryCollectionProcessor $recorder): Recorder
    {
        return new Recorder($recorder);
    }
}
