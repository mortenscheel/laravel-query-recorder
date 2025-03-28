<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Tests\Fixtures;

use Scheel\QueryRecorder\Processors\QueryCollectionProcessor;
use Scheel\QueryRecorder\QueryCollection;

class NullProcessor implements QueryCollectionProcessor
{
    public function process(QueryCollection $queries): void {}
}
