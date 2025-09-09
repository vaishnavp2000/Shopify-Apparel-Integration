<?php

namespace App\Jobs\ApparelMagic;

use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateApparelMagicProducts implements ShouldQueue
{
    use Queueable;
    use ApparelMagicHelper;
    protected $product;
    protected $productVariants;

    /**
     * Create a new job instance.
     */
    public function __construct($product,$productVariants)
    {
         $this->product=$product;
         $this->productVariants=$productVariants;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createAmProducts(  $this->product,$this->productVariants);
    }
}
