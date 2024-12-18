<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppWebhookController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function verify(Request $request)
    {
        return $this->whatsAppService->verifyWebhook($request);
    }

    public function handle(Request $request)
    {
        return $this->whatsAppService->handleIncomingWebhook($request);
    }
}
