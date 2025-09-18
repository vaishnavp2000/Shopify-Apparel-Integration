<?php

namespace App\Jobs\ApparelMagic;

use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetApparelMagicChartOfAccounts implements ShouldQueue
{
    use Queueable,ApparelMagicHelper;
     protected $setting;

    /**
     * Create a new job instance.
     */
      public function __construct($settings)
    {
        $this->setting = $settings;

    }

    /**
     * Execute the job.
     */
     public function handle(): void
    {
        $this->getApparelChartOfAccounts($this->setting);
    }
}
