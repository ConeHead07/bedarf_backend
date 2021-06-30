<?php

namespace App\Console\Commands;

use App\Notifications;
use Illuminate\Console\Command;

/*
 * * * * * * cd /usr/home/mertenna/public_html/sites/dev-inventory.mertens.services && php artisan barcode:findsend >> /dev/null 2>&1
 * * * * * * cd /usr/home/mertenna/public_html/sites/inventory.mertens.services && php artisan barcode:findsend >> /dev/null 2>&1
 */

class FindAndSendMalformedBarcodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'barcode:findsend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suchen und Versendung ungew�hnlicher Barcodes!';

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
        echo "findAndSendMalformedInvBarcodess\n";
        $aMalformedBarcodes = $modelNotifications->findAndSendMalformedInvBarcodes(600);
        $iNumMalformed = count($aMalformedBarcodes);
        echo "FOUND $iNumMalformed ungewoehlnliche Inventar-Barcodes!\n";
        echo implode("\n", $aMalformedBarcodes) . ($iNumMalformed > 0 ? "\n" : '');
        echo "finished\n";
    }
}
