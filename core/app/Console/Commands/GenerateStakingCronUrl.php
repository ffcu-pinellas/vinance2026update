<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class GenerateStakingCronUrl extends Command
{
    protected $signature = 'staking:cron-url';
    protected $description = 'Generate signed URL for staking rewards cron';

    public function handle()
    {
        $url = URL::signedRoute('cron.staking.rewards');
        $this->info("Staking Cron URL: " . $url);
        return 0;
    }
}