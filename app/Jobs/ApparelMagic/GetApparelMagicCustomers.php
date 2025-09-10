<?php

namespace App\Jobs\ApparelMagic;

use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetApparelMagicCustomers implements ShouldQueue
{
    use Queueable,ApparelMagicHelper;
     protected $page_size,$startAfter,$settings;

    /**
     * Create a new job instance.
     */
    public function __construct($page_size,$startAfter,$settings)
    {
        $this->page_size = $page_size;
        $this->startAfter = $startAfter;
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
    
        $this->getApparelCustomer($this->page_size,$this->startAfter,$this->settings);

    }
}
