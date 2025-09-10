<?php

namespace App\Jobs\ApparelMagic;

use App\Models\Order;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateApparelOrders implements ShouldQueue
{
    use Queueable,ApparelMagicHelper;
     protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
         $this->order=$order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createApparelmagicOrder(   $this->order);

    }
}
