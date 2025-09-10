<?php

namespace App\Jobs\ApparelMagic;

use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetApparelMagicDivisions implements ShouldQueue
{
     use Queueable,ApparelMagicHelper;
    protected $settings;
    /**
     * Create a new job instance.
     */
    public function __construct($settings)
    {
        $this->settings = $settings;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->getApparelDivision($this->settings);
    }
}
