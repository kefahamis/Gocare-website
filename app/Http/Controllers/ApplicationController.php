<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationReceived;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $application = Application::create([
            'reference' => 'GC-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'status' => 'submitted',
            'data' => $request->except(['_token']),
            'submitted_at' => now(),
        ]);

        Mail::to(config('gocare.notification_email'))->send(new ApplicationReceived($application));

        return response()->json([
            'message' => 'Application submitted successfully.',
            'reference' => $application->reference,
        ]);
    }
}
