<?php

namespace App\Console\Commands;

use App\Notifications;
use Illuminate\Console\Command;

/*
 * * * * * * cd /usr/home/mertenna/public_html/sites/dev-inventory.mertens.services && php artisan barcode:alerts >> /dev/null 2>&1
 * * * * * * cd /usr/home/mertenna/public_html/sites/inventory.mertens.services && php artisan barcode:alerts >> /dev/null 2>&1
 */

class BarcodeAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'barcode:alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kommando versendet in DB hinterlegte Barcode-Warnungen';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $modelNotifications = new Notifications();
        if (!$modelNotifications->hasNewBarcodeAlerts()) {
            echo "No new Barcode-Notfications\n";
            return;
        }
        $modelNotifications->sendBarcodeAlerts();
    }
}
