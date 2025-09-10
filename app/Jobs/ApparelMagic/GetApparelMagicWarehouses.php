<?php

namespace App\Jobs\ApparelMagic;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetApparelMagicWarehouses implements ShouldQueue
{
    use Queueable;
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
        $this->getAmWarehouses($this->settings);
    }
}
