<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PurchaseHistory;

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
        $userDataJsonFile = json_decode(file_get_contents($path), true);
        foreach ($userDataJsonFile as  $userData) {
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
        // dd($userDataJsonFile[0]['purchaseHistory'][0]);
        // return 0;
    }
}
