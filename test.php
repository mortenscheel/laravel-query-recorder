<?php

declare(strict_types=1);

use Scheel\QueryRecorder\QueryRecorder;

require 'vendor/autoload.php';

function run()
{
    return (new QueryRecorder)->example();
}

dd(run());
