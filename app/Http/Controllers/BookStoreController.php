<?php

namespace App\Http\Controllers;

use App\Models\BookStore;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookStoreController extends Controller
{
    //
    public function ListBookStoreOpenTime(Request $request)
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
}
