<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Recorders;

use Scheel\QueryRecorder\QueryCollection;

interface RecordsQueries
{
    public function recordQueries(QueryCollection $queries): void;
}
