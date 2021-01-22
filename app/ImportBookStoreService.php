<?php

namespace App;

use App\Models\Books;
use App\Models\BookStore;
use App\Models\BookStoreOpenTime;

class ImportBookStoreService
{
    public $weekArray = ['Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat'];
    public function importBookStore($JsonFile)
    {
        $weekArray = $this->weekArray;
        foreach ($JsonFile as $bookStores) {
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

            $bookStroeDB = BookStore::where('storeName', $bookStores['storeName'])->first();    #若bookStore剛剛建立 需要再次讀取
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
        ##
        $bookStores = BookStore::select('storeName', 'openingHours')->get();
        foreach ($bookStores as $bookStore) {
            $explodeArray = explode('/', $bookStore->openingHours);
            foreach ($explodeArray as  $explodeString) {
                $openWeeks = [];
                foreach ($weekArray as $WeekKey => $week) {
                    if (strpos($explodeString, $week) !== false) {
                        array_push($openWeeks, $WeekKey);
                    }
                }
                $explodeBySpeceArray = explode(' ', $explodeString);
                if (count($openWeeks) == 2 && array_search('-', $explodeBySpeceArray) == 1) {
                    for ($i = $openWeeks[0] + 1; $i < $openWeeks[1]; $i++) {
                        array_push($openWeeks, $i);
                    }
                }
                $timeArray = app('OutputTimeService')->outPutTime($explodeString);
                // dd($openWeeks);
                // dd($weekArray[$openWeeks[0]], $timeArray);
                // dd($openTimeDB);
                foreach ($openWeeks as $openWeek) {
                    $openTimeDB = BookStoreOpenTime::where('storeName', $bookStore->storeName)->first();
                    if ($openTimeDB == null) {
                        BookStoreOpenTime::create([
                            "storeName" => $bookStore->storeName,
                            "openTime$weekArray[$openWeek]" => $timeArray[0],
                            "closeTime$weekArray[$openWeek]" => $timeArray[1],
                        ]);
                    } else {
                        $openTimeDB->update([
                            "openTime$weekArray[$openWeek]" => $timeArray[0],
                            "closeTime$weekArray[$openWeek]" => $timeArray[1],
                        ]);
                    }
                }
            }
        }
    }
}
