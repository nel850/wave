<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsAppService
{
    protected $phoneNumberId;
    protected $accessToken;

    public function __construct()
    {
        $this->phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
        $this->accessToken = env('WHATSAPP_ACCESS_TOKEN');
    }

    /**
     * Sends a WhatsApp message.
     */
    public function sendMessage(string $recipientPhone, string $messageBody)
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->post("https://graph.facebook.com/v21.0/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $this->formatPhoneNumber($recipientPhone),
                    'type' => 'text',
                    'text' => ['body' => $messageBody],
                ]);

            if ($response->successful()) {
                $messageId = $response->json('messages.0.id');
                return ['success' => true, 'message_id' => $messageId];
            }

            Log::error('Failed to send WhatsApp message', [
                'response' => $response->json(),
                'recipient' => $recipientPhone,
            ]);

            return ['success' => false, 'error' => $response->json()];
        } catch (\Exception $e) {
            Log::error('WhatsApp message exception', [
                'error' => $e->getMessage(),
                'recipient' => $recipientPhone,
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Formats a phone number for WhatsApp API.
     */
    public function formatPhoneNumber(string $phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10) {
            return '254' . $phone;
        }

        return $phone;
    }

    /**
     * Verifies the webhook subscription.
     */
    public function verifyWebhook(Request $request)
    {
        $hubVerifyToken = $request->get('hub_verify_token');
        $hubChallenge = $request->get('hub_challenge');

        if ($hubVerifyToken === env('WHATSAPP_WEBHOOK_VERIFY_TOKEN')) {
            return response($hubChallenge, 200);
        }

        return response('Invalid verification token', 403);
    }

    /**
     * Handles incoming webhook notifications.
     */
    public function handleIncomingWebhook(Request $request)
{
    try {
        // Log the entire payload for debugging
        Log::info('Webhook Payload', ['payload' => $request->all()]);

        $response = $request->json()->all();

        // Extract key components
        $messages = $response['entry'][0]['changes'][0]['value']['messages'] ?? [];
        $metadata = $response['entry'][0]['changes'][0]['value']['metadata'] ?? [];
        $contacts = $response['entry'][0]['changes'][0]['value']['contacts'] ?? [];

        // Validate basic structure
        if (empty($messages)) {
            Log::warning('No messages in webhook payload');
            return response()->json(['status' => 'success']);
        }

        // Process each message
        foreach ($messages as $message) {
            // Extract phone number ID from metadata
            $phone_number_id = $metadata['phone_number_id'] ?? null;

            // Find matching contact
            $contact = $this->findMatchingContact($contacts, $message['from']);

            // Process the message
            $this->processMessage($message, $contact, $phone_number_id);
        }

        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        Log::error('Webhook processing error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'payload' => $request->all()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Processing failed: ' . $e->getMessage()
        ], 500);
    }
}

private function findMatchingContact(array $contacts, string $from): ?array
{
    // Check if contacts array is empty
    if (empty($contacts)) {
        Log::warning('No contacts provided in webhook payload', ['message_from' => $from]);
        return null;
    }

    // Match the contact based on wa_id
    foreach ($contacts as $contact) {
        if (($contact['wa_id'] ?? '') === $from) {
            Log::info('Matching contact found', [
                'message_from' => $from,
                'contact' => $contact
            ]);
            return $contact;
        }
    }

    // Log a warning if no match is found
    Log::warning('No matching contact found', [
        'message_from' => $from,
        'available_contacts' => array_column($contacts, 'wa_id')
    ]);

    return null;
}


private function processMessage(array $message, ?array $contact, ?string $phone_number_id)
{
    // Validate message type
    if (!isset($message['type']) || $message['type'] !== 'text') {
        Log::warning('Non-text message received', ['message' => $message]);
        return;
    }

    // Extract message details
    $from = $message['from'];
    $messageBody = $message['text']['body'] ?? '';
    $messageId = $message['id'] ?? null;
    $timestamp = $message['timestamp'] ?? now()->timestamp;

    DB::transaction(function () use ($from, $contact, $messageBody, $messageId, $timestamp) {
        // Check if recipient exists using wa_id
        $recipient = Recipient::where('wa_id', $from)->first();

        if (!$recipient) {
            // Create recipient if not exists
            $recipient = Recipient::create([
                'phone_number' => $from,
                'name' => $contact['profile']['name'] ?? 'Unknown',
                'wa_id' => $contact['wa_id'] ?? null
            ]);
        } else {
            Log::info('Existing recipient found', ['recipient_id' => $recipient->id]);
        }

        // Determine sender ID
        $senderId = Auth::check() ? Auth::user()->id : User::first()->id;

        // Check if conversation exists
        $conversation = Conversation::where(
            [
                'sender_id' => $senderId,
                'receiver_id' => $recipient->id,
                'conversation_type' => 'whatsapp'
            ]
        )->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'sender_id' => $senderId,
                'receiver_id' => $recipient->id,
                'conversation_type' => 'whatsapp'
            ]);
        } else {
            Log::info('Existing conversation found', ['conversation_id' => $conversation->id]);
        }

        // Create the message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $recipient->id,
            'body' => $messageBody,
            'message_id' => $messageId,
            'status' => 'received',
            'sent_at' => Carbon::createFromTimestamp($timestamp)
        ]);

        Log::info('Message processed successfully', [
            'from' => $from,
            'message_id' => $messageId
        ]);
    });
}


}
