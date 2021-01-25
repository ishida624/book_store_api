<?php

namespace App\Http\Controllers;

use App\Models\Books;
use App\Models\BookStore;
use App\Models\PurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\ValidationForTime;

class UserController extends Controller
{
    #輸入 dateFrom = {date} (example : 2020-11-21 )
    #輸入 dateTo = {date} (example : 2020-11-21 )
    #輸入 limit = {number} (type = integer)
    public function userListByHighestTransactionAmount(ValidationForTime $request)
    {
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $inputLimit = $request->limit;
        if (!ctype_digit($inputLimit)) {
            return response()->json(['status' => 'error', 'message' => 'inputLimit must be integer'], 400);
        }
        $totalTransactionAmount = DB::select("select * from 
        (select name,sum(transactionAmount) as sum
         from purchaseHistory as p join users as u on p.user_id=u.id 
         where transactionDate <= '$dateTo' && transactionDate >= '$dateFrom'
         group by user_id) as sub order by sum desc limit $inputLimit;");
        return $totalTransactionAmount;
    }
    #輸入 dateFrom = {date} (example : 2020-11-21 )
    #輸入 dateTo = {date} (example : 2020-11-21 )
    public function totalTransactionAmount(ValidationForTime $request)
    {
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $totalTransactionAmount = DB::select("select count(id) as NumberOfTransaction,sum(transactionAmount) as sumDollar 
        from purchaseHistory
        where transactionDate <= '$dateTo' && transactionDate >= '$dateFrom';");
        return $totalTransactionAmount;
    }
    #輸入transactionValueOrNumber = number or value (依照交易總額或是數量做排名)
    public function popularStore(Request $request)
    {
        $transactionValueOrNumber = $request->transactionValueOrNumber;
        if ($transactionValueOrNumber == 'value') {
            $popular = DB::select("select storeName,sum(transactionAmount) as sum from purchaseHistory 
             group by storeName order by sum desc limit 1;");
        } elseif ($transactionValueOrNumber == 'number') {
            $popular = DB::select("select storeName,count(transactionAmount) as count 
            from purchaseHistory as p group by storeName order by count desc limit 1;");
        } else {
            return response()->json(['status' => 'error', 'message' => 'transactionValueOrNumber value must be value or number'], 400);
        }
        return $popular;
    }
    #輸入 dateFrom = {date} (example : 2020-11-21 )
    #輸入 dateTo = {date} (example : 2020-11-21 )
    #輸入 moreOrLess = more or less
    #輸入 transactionSun = {number}(type=float)
    public function numberOfUserByTransaction(ValidationForTime $request)
    {
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $moreOrLess = $request->moreOrLess;
        $transactionSum = $request->transactionSum;
        if ($moreOrLess == 'more') {
            $sumParam = ">=$transactionSum";
        } elseif ($moreOrLess == 'less') {
            $sumParam = "<=$transactionSum";
        } else {
            return response()->json(['status' => 'error', 'message' => 'moreOrLess value must be more or less'], 400);
        }
        if (!is_numeric($transactionSum)) {
            return response()->json(['status' => 'error', 'message' => 'transactionSum value type must be float'], 400);
        }
        $peopleCount = DB::select("select count(sum) as numberOfUser from
         (select sum(transactionAmount) as sum from purchaseHistory 
          where transactionDate <= '$dateTo' && transactionDate >= '$dateFrom'
         group by user_id having sum$sumParam) as sub;");
        return $peopleCount;
    }

    #輸入userName = {userName}(type = string)
    #輸入bookName = {bookName}(type = string)
    public function buyBook(Request $request)
    {
        $userName = $request->userName;
        $bookName = $request->bookName;
        $user = User::where('name', $userName)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'userName is wrong'], 400);
        }
        $bookData = Books::where('bookName', $bookName)->first();
        if (!$bookData) {
            return response()->json(['status' => 'error', 'message' => 'bookName is wrong'], 400);
        }
        try {
            DB::transaction(
                function () use ($bookData, $user) {
                    $price = $bookData->price;
                    $bookStore = $bookData->showStore;
                    $now = Carbon::now();
                    $userCashBalance = $user->cashBalance;
                    $bookStoreCashBalance = $bookStore->cashBalance;
                    $bookStore->update(['cashBalance' => $bookStoreCashBalance + $price]);
                    $user->update(['cashBalance' => $userCashBalance - $price]);
                    PurchaseHistory::create([
                        'bookName' => $bookData->bookName,
                        'storeName' => $bookStore->storeName,
                        'transactionAmount' => $price,
                        'transactionDate' => $now,
                        'user_id' => $user->id,
                    ]);
                }
            );
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'transaction error'], 400);
        }
        return response()->json(['status' => 'OK', 'message' => 'transaction successfully'], 200);
    }

    #輸入userName = [{要更改的userName},{要改成什麼userName}]
    #輸入bookName = [{要更改的bookName},{要改成什麼bookName}]
    #輸入price = {number} (type = float)
    #輸入storeName = [{要更改的storeName},{要改成什麼storeName}]
    public function updateUserBookData(Request $request)
    {
        $userName = $request->userName;
        $bookName = $request->bookName;
        $price = $request->price;
        $storeName = $request->storeName;

        DB::beginTransaction();
        if (isset($userName[0]) && isset($userName[1])) {
            $user = User::where('name', $userName[0])->first();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'user not found'], 400);
            }
            if (null !== (User::where('name', $userName[1])->first())) {
                return response()->json(['status' => 'error', 'message' => 'userName already exists'], 400);
            }
            $user->update(['name' => $userName[1]]);
        }
        if (isset($bookName[0]) && isset($bookName[1])) {
            $book = Books::where('bookName', $bookName[0])->first();
            if (!$book) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'book not found'], 400);
            }
            if (null !== (Books::where('bookName', $bookName[1])->first())) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'bookName already exists'], 400);
            }
            $book->update(['bookName' => $bookName[1]]);
            DB::select("update purchaseHistory set bookName = '$bookName[1]' where bookName = '$bookName[0]'");
            if (isset($price)) {
                if (!is_numeric($price)) {
                    return response()->json(['status' => 'error', 'message' => 'price value type must be double or integer'], 400);
                }
                $book->update(['price' => $price]);
            }
        }
        if (isset($storeName[0]) && isset($storeName[1])) {
            $bookStore = BookStore::where('storeName', $storeName[0])->first();
            if (!$bookStore) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'book not found'], 400);
            }
            if (null !== (BookStore::where('storeName', $storeName[1])->first())) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'storeName already exists'], 400);
            }
            $bookStore->update(['storeName' => $storeName[1]]);
            DB::select("update purchaseHistory set storeName = '$storeName[1]' where storeName = '$storeName[0]'");
        }
        DB::commit();
        return response()->json(['status' => 'OK', 'message' => 'update successfully'], 200);
    }
}
