<?php

namespace App\Console\Commands;

use App\Services\ModerationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('moderation:expire-suspensions')]
#[Description('End temporary suspensions whose time is up and restore access')]
class ExpireSuspensions extends Command
{
    public function handle(ModerationService $moderation): int
    {
        $count = $moderation->expireSuspensions();
        $this->info("{$count} suspension(s) expired.");

        return self::SUCCESS;
    }
}
