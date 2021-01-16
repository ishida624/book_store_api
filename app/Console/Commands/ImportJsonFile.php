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

            foreach ($JsonFile as  $userData) {
                # 尋找資料庫內有無一樣的資料，沒有就新建立，有就更新
                $userDataDB = User::where('name', $userData['name'])->first();
                if (!$userDataDB) {
                    User::create([
                        'cashBalance' => $userData['cashBalance'],
                        'name' => $userData['name'],
                    ]);
                } else {
                    $userDataDB->update([
                        'cashBalance' => $userData['cashBalance'],
                        'name' => $userData['name'],
                    ]);
                }

                $userDataDB = User::where('name', $userData['name'])->first();
                foreach ($userData['purchaseHistory'] as  $purchaseHistory) {
                    $purchaseHistoryDB = PurchaseHistory::where('bookName', $purchaseHistory['bookName'])->first();
                    if (!$purchaseHistoryDB) {
                        PurchaseHistory::create([
                            'bookName' => $purchaseHistory['bookName'],
                            'storeName' => $purchaseHistory['storeName'],
                            'transactionAmount' => $purchaseHistory['transactionAmount'],
                            'transactionDate' => $purchaseHistory['transactionDate'],
                            'user_id' => $userDataDB->id,
                        ]);
                    } else {
                        $purchaseHistoryDB->update([
                            'bookName' => $purchaseHistory['bookName'],
                            'storeName' => $purchaseHistory['storeName'],
                            'transactionAmount' => $purchaseHistory['transactionAmount'],
                            'transactionDate' => $purchaseHistory['transactionDate'],
                            'user_id' => $userDataDB->id,
                        ]);
                    }
                }
            }
            // echo ('input user data');
        } elseif ($action === 'import_data:book_store') {
            foreach ($JsonFile as $bookStores) {
                # code...
                $bookStroeDB = BookStore::where('storeName', $bookStores['storeName'])->first();
                if (!$bookStroeDB) {
                    BookStore::create([
                        'storeName' => $bookStores['storeName'],
                        'openingHours' => $bookStores['openingHours'],
                        'cashBalance' => $bookStores['cashBalance'],
                    ]);
                } else {
                    $bookStroeDB->update([
                        'storeName' => $bookStores['storeName'],
                        'openingHours' => $bookStores['openingHours'],
                        'cashBalance' => $bookStores['cashBalance'],
                    ]);
                }

                $bookStroeDB = BookStore::where('storeName', $bookStores['storeName'])->first();
                foreach ($bookStores['books'] as $book) {
                    $bookDB = Books::where('bookName', $book['bookName'])->first();
                    if (!$bookDB) {
                        Books::create([
                            'bookName' => $book['bookName'],
                            'price' => $book['price'],
                            'bookStore_id' => $bookStroeDB->id,
                        ]);
                    } else {
                        $bookDB->update([
                            'bookName' => $book['bookName'],
                            'price' => $book['price'],
                            'bookStore_id' => $bookStroeDB->id,
                        ]);
                    }
                }
            }
        } else {
            $this->info('action is wrong');
        }
    }
}
