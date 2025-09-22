<?php

namespace App\Jobs\ApparelMagic;

use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetApparelMagicInventory implements ShouldQueue
{
    use Queueable,ApparelMagicHelper;
    protected $settings;
   protected $startAfter;
   protected $pageSize;
    

    /**
     * Create a new job instance.
     */
    public function __construct($settings,$startAfter,$pageSize)
    {
       $this->settings=$settings;
       $this->startAfter=$startAfter;
       $this->pageSize=$pageSize;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $this->getApparelWareHouseStock($this->settings, $this->startAfter, $this->pageSize);
    }
}
