<?php

namespace App;

use App\Models\User;
use App\Models\PurchaseHistory;

class ImportUserDataService
{

    public function importUserData($JsonFile)
    {
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
    }
}
