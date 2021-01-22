<?php

namespace App\Http\Controllers;

use App\Models\BookStore;
use App\Models\Books;
use App\Models\BookStoreOpenTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookStoreController extends Controller
{
    public $weekArray = ['Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat', 'Sun'];
    public function listBookStoreOpenTime(Request $request)
    {
        $week = $request->week;
        $time = $request->time;
        try {
            Carbon::parse($time);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'time format is wrong'], 400);
        }
        if (!in_array($week, $this->weekArray)) {
            return response()->json(['status' => 'error', 'message' => 'week  format is wrong'], 400);
        }
        $inputTime = Carbon::parse($time);
        // $bookStores = BookStore::where('openingHours', 'like', "%$week%")->get();
        // foreach ($bookStores as $key => $bookStore) {
        //     $explode = explode('/', $bookStore->openingHours);  #將字串以/切開
        //     foreach ($explode as $explodeString) {
        //         if (strpos($explodeString, $week) !== false) {      #搜尋字串中是否有$week
        //             $timeArray = app('OutputTimeService')->outPutTime($explodeString);
        //         }
        //     }
        //     if (!($inputTime >= $timeArray[0] && $inputTime <= $timeArray[1])) {
        //         $bookStores->forget($key);
        //     }
        // }
        // dd("openTime$week");
        $bookStores = BookStoreOpenTime::select('storeName')
            ->where("openTime$week", '<=', $inputTime)
            ->where("closeTime$week", '>=', $inputTime)
            ->get();
        return response()->json($bookStores);
    }
    public function listBookStoreDayOfWeek(Request $request)
    {
        $week = $request->query('week');
        if (array_search($week, $this->weekArray) === false) {
            return response()->json(['status' => 'error', 'message' => 'week  format is wrong'], 400);
        }
        $bookStores = BookStore::where('openingHours', 'like', "%$week%")->get();
        return response()->json($bookStores);
    }
    public function ListBookStoreFilterByTotalTime(Request $request)
    {
        $dayOrWeek = $request->dayOrWeek;
        $totalTime = $request->totalTime;
        $moreOrLess = $request->moreOrLess;
        $weekArray = $this->weekArray;
        if ($moreOrLess !== 'more' && $moreOrLess !== 'less') {
            return response()->json(['status' => 'error', 'message' => 'moreOrLess value must be more or less'], 400);
        }

        $bookStores = BookStoreOpenTime::all();
        if ($dayOrWeek == 'allWeek') {
            foreach ($bookStores as $key => $bookStore) {
                $timeSum = 0;
                foreach ($weekArray as $week) {
                    $openParam = 'openTime' . $week;
                    $closeParam = 'closeTime' . $week;
                    $openTime = $bookStore->$openParam;
                    $closeTime = $bookStore->$closeParam;
                    $timediff = $closeTime->diffInMinutes($openTime);
                    $timeSum = $timeSum + $timediff;
                }
                if ($moreOrLess === 'more') {
                    if ($timeSum / 60 < $totalTime) {
                        $bookStores->forget($key);
                    }
                } else {
                    if ($timeSum / 60 > $totalTime) {
                        $bookStores->forget($key);
                    }
                }
                if (isset($bookStores[$key])) {
                    $storeName[] = $bookStore->only('storeName');
                }
            }
        } elseif (in_array($dayOrWeek, $weekArray)) {
            foreach ($bookStores as $key2 => $bookStore) {
                foreach ($weekArray as $week) {
                    $openParam = 'openTime' . $week;
                    $closeParam = 'closeTime' . $week;
                    $openTime = $bookStore->$openParam;
                    $closeTime = $bookStore->$closeParam;
                    $timediff = $closeTime->diffInMinutes($openTime);
                }
                if ($moreOrLess === 'more') {
                    if ($timediff / 60 < $totalTime) {
                        $bookStores->forget($key2);
                    }
                } else {
                    if (
                        $timediff / 60 > $totalTime
                    ) {
                        $bookStores->forget($key2);
                    }
                }
                if (isset($bookStores[$key2])) {
                    $storeName[] = $bookStore->only('storeName');
                }
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'dayOrWeek  format is wrong'], 400);
        }
        return response()->json($storeName);
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
        if (is_numeric($price)) {
            $numberOfBook = (float)$numberOfBook;
            $wherePrice = "where price < $price";
        } elseif ($price == null) {
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
