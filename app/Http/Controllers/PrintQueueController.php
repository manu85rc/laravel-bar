<?php

namespace App\Http\Controllers;

use App\Models\PrintQueueJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintQueueController extends Controller
{
    public function poll(Request $request): JsonResponse
    {
        $printerId = $request->query('printer_id');

        if (! $printerId) {
            return response()->json(['error' => 'printer_id required'], 400);
        }

        $jobs = PrintQueueJob::where('printer_id', $printerId)
            ->where('pending', true)
            ->orderBy('created_at')
            ->get();

        return response()->json($jobs);
    }

    public function complete(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:print_queue,id',
        ]);

        PrintQueueJob::where('id', $request->input('id'))->update(['pending' => false]);

        return response()->json(['success' => true]);
    }
}
