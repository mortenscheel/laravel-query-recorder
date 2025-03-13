<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Processor;

use Scheel\QueryRecorder\RecordedQuery;

interface QueryRecordProcessor
{
    /** @param array<int, RecordedQuery> $records */
    public function process(array $records): void;
}
