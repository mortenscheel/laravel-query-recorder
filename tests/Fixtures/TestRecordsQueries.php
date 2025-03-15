<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Tests\Fixtures;

use Scheel\QueryRecorder\QueryCollection;
use Scheel\QueryRecorder\Recorders\RecordsQueries;

class TestRecordsQueries implements RecordsQueries
{
    public bool $called = false;

    /** @var \Scheel\QueryRecorder\RecordedQuery[] */
    public array $records = [];

    public function recordQueries(QueryCollection $queries): void
    {
        $this->called = true;
        $this->records = $queries->all();
    }
}
