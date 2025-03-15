<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Tests\Fixtures;

use Illuminate\Support\Facades\DB;

class DummyClass
{
    public function doStuff(): void
    {
        $this->query();
    }

    public function query(): void
    {
        DB::table('test_table')->where('id', '>=', 10)->get();
    }

    public function getById(int $id): void
    {
        DB::table('test_table')->where('id', $id)->get();
    }
}
