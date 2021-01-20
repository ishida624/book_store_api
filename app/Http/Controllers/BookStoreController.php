<?php

namespace App\Http\Controllers;

use App\Models\BookStore;
use App\Models\Books;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookStoreController extends Controller
{
    public function listBookStoreOpenTime(Request $request)
    {
        $week = $request->query('week');
        $time = $request->query('time');
        try {
            Carbon::parse($time);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'time format is wrong'], 400);
        }
        if (array_search($week, ['Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat', 'Sun']) === false) {
            return response()->json(['status' => 'error', 'message' => 'week  format is wrong'], 400);
        }
        $inputTime = Carbon::parse($time)->format('H:i:s');
        $bookStores = BookStore::where('openingHours', 'like', "%$week%")->get();
        foreach ($bookStores as $key => $bookStore) {
            $explode = explode('/', $bookStore->openingHours);
            foreach ($explode as $explodeString) {
                if (strpos($explodeString, $week) !== false) {
                    $explode2 = explode(' ', $explodeString);
                    if (array_search('pm', $explode2) && isset($explode2[array_search('pm', $explode2) + 2])) {
                        $startTimeStringKey = array_search('pm', $explode2);
                    } else {
                        $startTimeStringKey = array_search('am', $explode2);
                    }
                    $startTimeString = $explode2[$startTimeStringKey - 1] . $explode2[$startTimeStringKey];
                    $closeTimeString = $explode2[$startTimeStringKey + 2] . $explode2[$startTimeStringKey + 3];
                }
            }
            $closeTime = Carbon::parse($closeTimeString)->format('H:i:s');
            $startTime = Carbon::parse($startTimeString)->format('H:i:s');
            if (!($inputTime >= $startTime && $inputTime <= $closeTime)) {
                $bookStores->forget($key);
            }
        }
        return response()->json($bookStores);
    }
    public function listBookStoreDayOfWeek(Request $request)
    {
        $week = $request->query('week');
        if (array_search($week, ['Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat', 'Sun']) === false) {
            return response()->json(['status' => 'error', 'message' => 'week  format is wrong'], 400);
        }
        $bookStores = BookStore::where('openingHours', 'like', "%$week%")->get();
        return response()->json($bookStores);
    }
    public function ListBookStoreMoreOrLessTime(Request $request)
    {
        # code...

    }
    public function listBooks(Request $request)
    {
        $price = (float)$request->price;
        $orderBy = $request->orderBy;
        if (!$price) {
            return response()->json(['status' => 'error', 'message' => 'price value type must be double or integer'], 400);
        }
        if ($orderBy === 'price') {
            $books = Books::where('price', '<', $price)->orderby('price')->get();
        } elseif ($orderBy === 'bookName') {
            $books = Books::where('price', '<', $price)->orderby('bookName')->get();
        } else {
            return response()->json(['status' => 'error', 'message' => 'orderBy value must be price or bookName'], 400);
        }
        return response()->json($books);
    }

    public function listBookStoreFilterBooksAndPrice(Request $request)
    {
        $numberOfBook = $request->numberOfBook;
        $moreOrLess = $request->moreOrLess;
        $price = $request->price;
        if (ctype_digit($numberOfBook)) {
            $numberOfBook = (int)$numberOfBook;
        } else {
            return response()->json(['status' => 'error', 'message' => 'numberOfBook value type must be integer'], 400);
        }
        if (floatval($price)) {
            $numberOfBook = (float)$numberOfBook;
            $wherePrice = "where price < $price";
        } elseif ($price == null) {
            # code...
            $wherePrice = '';
        } else {
            return response()->json(['status' => 'error', 'message' => 'price value type must be float'], 400);
        }

        if ($moreOrLess == 'more') {
            $queryParam = ">$numberOfBook";
        } elseif ($moreOrLess == 'less') {
            $queryParam = "<$numberOfBook";
        } else {
            return response()->json(['status' => 'error', 'message' => 'moreOrLess value must be more or less'], 400);
        }
        $bookStore = DB::select("select storeName,count(bookName) as number_of_books from bookStore as s join books as b on s.id=b.bookStore_id $wherePrice group by storeName having count(bookName)$queryParam;");
        return response()->json($bookStore);
    }
}
