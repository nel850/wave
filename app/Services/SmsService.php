<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\MessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SmsService
{
    protected $apiBaseUrl;
    protected $apiKey;
    protected $senderId;

    public function __construct()
    {
        $this->apiBaseUrl = env('INFOBIP_BASE_URL');
        $this->apiKey = env('INFOBIP_API_KEY');
        $this->senderId = env('INFOBIP_SENDER_ID');
    }

    /**
     * Sends an SMS message via Infobip.
     */


        public function sendMessage(string $recipientPhone, string $messageBody)
        {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "App {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])->post("{$this->apiBaseUrl}/sms/2/text/single", [
                    'from' => $this->senderId,
                    'to' => $this->formatPhoneNumber($recipientPhone),
                    'text' => $messageBody,
                ]);

        if ($response->successful()) {
            $messageId = $response->json('messages.0.id');
            return ['success' => true, 'message_id' => $messageId];
        }

        Log::error('Failed to send SMS via Infobip', [
            'response' => $response->json(),
            'recipient' => $recipientPhone,
        ]);

        return [
            'success' => false,
            'error' => $response->json(),
        ];
    } catch (\Exception $e) {
        Log::error('SMS send exception via Infobip', [
            'error' => $e->getMessage(),
            'recipient' => $recipientPhone,
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
    }



    /**
     * Handles incoming SMS webhook notifications from Infobip.
     */
    public function handleIncomingWebhook(Request $request)
{
    try {
        $data = $request->json()->all();

        // Check if the payload has the expected keys
        if (isset($data['message'], $data['sender'], $data['receiver'])) {
            $this->processIncomingMessage($data); // Pass the entire payload
        } else {
            Log::warning('Unexpected SMS Webhook payload structure', $data);
            return response()->json(['status' => 'invalid payload'], 400);
        }

        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        Log::error('SMS Webhook processing error via Infobip', [
            'error' => $e->getMessage(),
            'payload' => $request->all(),
        ]);

        return response()->json(['status' => 'error'], 500);
    }
}

    /**
     * Processes an incoming SMS message from Infobip.
     */
    private function processIncomingMessage(array $message)
    {
        DB::transaction(function () use ($message) {
            // Extract relevant details from the message payload
            $from = $message['sender'];
            $body = $message['message'];
            $to = $message['receiver'];
            $timestamp = now(); // Use current timestamp if none is provided in the payload

            // Check if a recipient exists with the same `wa_id` as `$from`
            $recipient = Recipient::where('wa_id', $from)->first();

            if (!$recipient) {
                // If recipient doesn't exist, create a new one
                $recipient = Recipient::create([
                    'phone_number' => $from,
                    'wa_id' => $from,
                    'name' => 'Unknown',
                ]);
            }

            // Determine sender ID (default to the first user if no authenticated user exists)
            $senderId = Auth::check() ? Auth::user()->id : User::first()->id;

            // Find or create the conversation between the sender and recipient
            $conversation = Conversation::firstOrCreate(
                [
                    'sender_id' => $senderId,
                    'receiver_id' => $recipient->id,
                    'conversation_type' => 'sms',
                ]
            );

            // Create the message within the conversation
            $newMessage = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'receiver_id' => $recipient->id,
                'body' => $body,
                'status' => 'received',
                'sent_at' => $timestamp, // Use timestamp from payload or default to now
            ]);

            $user = User::find($senderId);

            //send the notification
            if ($user) {
                Log::info('Sending notification to user', ['user_id' => $user->id]);

                $user->notify(new MessageReceived($newMessage, $conversation->id));

                Log::info('Notification sent successfully', ['user_id' => $user->id]);
            }else {
                Log::warning('User not found for senderId', ['sender_id' => $senderId]);
            }
        });
    }

    /**
     * Formats a phone number to international format.
     */
    private function formatPhoneNumber(string $phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10) {
            return '254' . $phone;
        }

        return $phone;
    }
}



//*
 //curl -X POST -H "Connelson@nelson-ThinkPad-X1-Carbon-3rd:~$ curl -X POST -H "Content-Type: application/json" -d '{"message": "Hello World", "sender": "254748442693", "receiver": "447491163443"}' https://aaa1-146-70-202-38.ngrok-free.app/sms/receive
//
//
//since a phone is unable to send http requests directly to my webhook thats why i am using the above curl function
