<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Scheel\QueryRecorder\Processors\QueryCollectionProcessor;
use Scheel\Tracer\StackTrace;

use function Illuminate\Support\defer;

class Recorder
{
    private readonly QueryCollection $queries;

    public function __construct(private readonly QueryCollectionProcessor $processor)
    {
        $this->queries = new QueryCollection;
        DB::listen(function (QueryExecuted $event): void {
            if ($frame = StackTrace::getTrace()->ignoreVendor()->ignoreFile(__FILE__)->first()) {
                $this->queries->push(new RecordedQuery($event->toRawSql(), $event->time, $frame));
            }
        });
        defer(function (): void {
            $this->processor->process($this->queries);
        });
    }

    public function getQueries(): QueryCollection
    {
        return $this->queries;
    }
}
