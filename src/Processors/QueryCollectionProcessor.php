<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Processors;

use Scheel\QueryRecorder\QueryCollection;

interface QueryCollectionProcessor
{
    public function process(QueryCollection $queries): void;
}
