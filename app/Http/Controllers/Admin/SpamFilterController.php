<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpamFilter;
use Exception;
use Illuminate\Http\Request;

class SpamFilterController extends Controller
{
    public function fetchSpamWords(Request $request)
    {
        try {
            $spam_filters = SpamFilter::select('*');

            if($request->has('status') && !is_null($request->status)) {
                $spam_filters->where('status', $request->status);
            }

            if($request->has('paginated') && $request->paginated == true) {
               $spam_filters = $spam_filters->paginate(30);
            } else {
                $spam_filters = $spam_filters->get();
            }

            if($request->has('extract') && $request->extract == true) {
                $spam_filters = $spam_filters->pluck('word')->toArray();
            }

            return response()->json([
                'status' => 'success',
                'data' => $spam_filters
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function addSpamWord(Request $request)
    {
        $request->validate(['word' => 'required|string']);

        try {
            $spam_filter = SpamFilter::create([
                'word' => $request->word,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Spam Word added successfully',
                'data' => $spam_filter
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function activateSpamWord($id)
    {
        try {
            
            $spam_filter = SpamFilter::where('id', $id)->first();
            
            if(!$spam_filter) {
                throw new Exception('Spam Word not found!');
            }
            
            SpamFilter::select('*')->update(['status' => 'inactive']);

            $spam_filter->status = 'active';
            $spam_filter->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Spam Word status updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateSpamWord(Request $request, $id)
    {
        try {
            $request->validate([
                'word' => 'required'
            ]);

            $spam_filter = SpamFilter::where('id', $id)->first();
            
            if(!$spam_filter) {
                throw new Exception('Spam word not found!');
            }

            $spam_filter->word = $request->word;
            $spam_filter->status = $request->status;
            $spam_filter->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Spam word updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
