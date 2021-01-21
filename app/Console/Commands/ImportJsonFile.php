<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PurchaseHistory;
use App\Models\BookStore;
use App\Models\Books;

class ImportJsonFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rake {action} {--path=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {

        $action = $this->argument('action');
        $path = $this->option('path');

        $JsonFile = json_decode(file_get_contents($path), true);

        if ($action === 'import_data:user') {      #判斷指令的action是什麼
            app('ImportUserDataService')->importUserData($JsonFile);
        } elseif ($action === 'import_data:book_store') {
            app('ImportBookStoreService')->importBookStore($JsonFile);
        } else {
            $this->info('action is wrong');
        }
    }
}
