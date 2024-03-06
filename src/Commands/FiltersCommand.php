<?php

namespace ahmmmmad11\Filters\Commands;

use Illuminate\Console\Command;

class FiltersCommand extends Command
{
    public $signature = 'filters';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
