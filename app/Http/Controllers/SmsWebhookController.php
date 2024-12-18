<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsWebhookController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handle(Request $request)
    {
        return $this->smsService->handleIncomingWebhook($request);
    }

    // public function deliveryReport(Request $request)
    // {
    //     return $this->smsService->handleDeliveryReport($request);
    // }
}
