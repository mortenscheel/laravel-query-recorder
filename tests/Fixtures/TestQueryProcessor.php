<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Tests\Fixtures;

use Scheel\QueryRecorder\Processor\QueryRecordProcessor;

class TestQueryProcessor implements QueryRecordProcessor
{
    public bool $called = false;

    /** @var \Scheel\QueryRecorder\RecordedQuery[] */
    public array $records = [];

    public function process(array $records): void
    {
        $this->called = true;
        $this->records = $records;
    }
}
