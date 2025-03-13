<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder;

use Scheel\QueryRecorder\Trace\Frame;

class RecordedQuery
{
    public function __construct(
        public string $sql,
        public float $time,
        public Frame $origin
    ) {}
}
