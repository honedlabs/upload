<?php

namespace Conquest\Upload\Commands;

use Illuminate\Console\Command;

class UploadCommand extends Command
{
    public $signature = 'upload';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
