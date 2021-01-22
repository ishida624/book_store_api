<?php

namespace App;

use Carbon\Carbon;

class OutPutTimeService
{
    public function outPutTime($string)
    {
        $explode = explode(' ', $string);         #以控格切開
        if (array_search('pm', $explode) && isset($explode[array_search('pm', $explode) + 2])) {
            $startTimeStringKey = array_search('pm', $explode);
        } else {
            $startTimeStringKey = array_search('am', $explode);
        }
        $startTimeString = $explode[$startTimeStringKey - 1] . $explode[$startTimeStringKey];
        $closeTimeString = $explode[$startTimeStringKey + 2] . $explode[$startTimeStringKey + 3];
        $closeTime = Carbon::parse($closeTimeString);
        $startTime = Carbon::parse($startTimeString);
        return array($startTime, $closeTime);
    }
}
