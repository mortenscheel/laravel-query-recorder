<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder;

use Illuminate\Support\Collection;

/** @extends Collection<int, \Scheel\QueryRecorder\RecordedQuery> */
class QueryCollection extends Collection {}
