<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Models\UserCredit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function fetchUserSmsInfo()
    {
        $total_sms_sent = SmsMessage::where(['user_id' => request()->user()->id, 'message_type' => 'normal'])->count();
        $remaining_credits = UserCredit::where('user_id', request()->user()->id)->first()->credit_balance;

        return response()->json([
            'status' => 'success',
            'data' => ['total_sms' => $total_sms_sent, 'credit' => $remaining_credits]
        ], 200);
    }

    public function fetchUserSmsChartData() 
    {
        $days = [];
        $smsCounts = [];

        // Define the end date as yesterday
        $endDate = Carbon::yesterday();

        // Define the start date as 14 days before yesterday
        // This gives you 14 full days before today.
        $startDate = $endDate->copy()->subDays(13); // subDays(13) because $endDate is already yesterday.
                                                  // If you want 14 days *before* yesterday, use subDays(14)
                                                  // To get 14 full days *including* yesterday, this is correct.

        // Fetch data from the database
        // We select the date formatted as 'MM/DD' and count messages for that date
        $dailySmsData = SmsMessage::selectRaw("DATE_FORMAT(created_at, '%m/%d') as day_formatted, COUNT(*) as count")
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy('day_formatted')
            ->orderBy('day_formatted', 'ASC') // Order by date to ensure correct chronological order
            ->get();

        // Create a map for easy lookup
        $smsCountMap = [];
        foreach ($dailySmsData as $data) {
            $smsCountMap[$data->day_formatted] = $data->count;
        }

        // Populate the 'days' and 'sms_count' arrays for the last 14 days
        // This ensures all 14 days are present, even if no SMS were sent on a particular day (count will be 0)
        for ($i = 0; $i < 14; $i++) {
            $currentDay = $startDate->copy()->addDays($i);
            $formattedDay = $currentDay->format('m/d');

            $days[] = $formattedDay;
            $smsCounts[] = $smsCountMap[$formattedDay] ?? 0; // Use null coalescing to default to 0 if no entry for that day
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'days' => $days,
                'sms_count' => $smsCounts,
            ]
        ]);
    }
}
