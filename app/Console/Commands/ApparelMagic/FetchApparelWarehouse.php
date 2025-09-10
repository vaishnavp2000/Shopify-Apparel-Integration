<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\GetApparelMagicWarehouses;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchApparelWarehouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-apparel-warehouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings=Setting::where('type','apparelmagic')->where('status',1)->get();
        GetApparelMagicWarehouses::dispatch($settings);

    }
}
