<?php

namespace App\Console\Commands;

use App\Notifications;
use Illuminate\Console\Command;

/*
 * * * * * * cd /usr/home/mertenna/public_html/sites/dev-inventory.mertens.services && php artisan barcode:findsend >> /dev/null 2>&1
 * * * * * * cd /usr/home/mertenna/public_html/sites/inventory.mertens.services && php artisan barcode:findsend >> /dev/null 2>&1
 */

class Tests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tests:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Code-Tests im App-Context';

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
        echo "Test\n";
        $created_at_before = '2021-05-18 21:05:29.000000';
        $created_at_after = date('Y-m-d H:i:s', strtotime($created_at_before));
        echo "\$created_at_before: $created_at_before\n";
        echo "\$created_at_after: $created_at_after\n";

        $created_at_before = '2021-05-19T07:57:23.457Z';
        $created_at_after = date('Y-m-d H:i:s', strtotime($created_at_before));
        echo "\$created_at_before: $created_at_before\n";
        echo "\$created_at_after: $created_at_after\n";

        $created_at_before = '2021-05-19T13:49:01.256Z';
        $created_at_after = gmdate('Y-m-d H:i:s', strtotime($created_at_before));
        echo "\$created_at_before: $created_at_before\n";
        echo "\$created_at_after: $created_at_after AS gmdate\n";

        $created_at_before = '2021-05-19T13:49:01.256';
        $created_at_after = gmdate('Y-m-d H:i:s', strtotime($created_at_before));
        echo "\$created_at_before: $created_at_before\n";
        echo "\$created_at_after: $created_at_after AS gmdate\n";


        $date = new \DateTime();
        $tz = date_timezone_get($date);
        echo 'timezone_name_get: ' . timezone_name_get($tz) . "\n";

    }
}
