<?php

namespace App;

use App\Models\Books;
use App\Models\BookStore;

class ImportBookStoreService
{

    public function importBookStore($JsonFile)
    {
        # code...
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
    }
}
