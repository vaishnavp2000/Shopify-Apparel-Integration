<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\GetApparelMagicInventory;
use App\Models\Setting;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Console\Command;

class FetchApparelInventory extends Command
{
    use ApparelMagicHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-apparel-stock';

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
        $settings=Setting::where('type', 'apparelmagic')->where('status', 1)->get();
        GetApparelMagicInventory::dispatch($settings,$startAftter=null,$pageSize=100);
    }
}
