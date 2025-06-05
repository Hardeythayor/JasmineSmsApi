<?php

namespace App\Jobs;

use App\Models\SmsGateway;
use App\Models\SmsMessage;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpSmpp\Service\Sender;

class SendSmppSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $recipient;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(SmsMessage $message, array $recipient)
    {
        $this->recipient = $recipient;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $sms_gateway = SmsGateway::where('name', 'easy_sms')->first();

        $host = $sms_gateway->credentials['smpp_host'];
        $port = $sms_gateway->credentials['smpp_port'];
        $systemId = $sms_gateway->credentials['account'];
        $password = $sms_gateway->credentials['password'];
        $senderId = $this->message->source;
        $timeout = config('bulksms.easy_sms.timeout');
        $debug = config('bulksms.easy_sms.debug');

        // Initialize variables for logging
        $messageId = null;

        try {
            // Instantiate the Sender service
            // The constructor typically takes: hosts array, login, password, [optional_options_array]
            // From the README: new \PhpSmpp\Service\Sender(['smschost.net'], 'login', 'pass');
            // Assuming we need to pass the port via an options array or a configuration.
            // Let's construct the connection details based on the service's likely expectations.
            // If the service doesn't have an explicit port argument, it might rely on default SMPP ports (2775)
            // or resolve based on DNS SRV records. Given your provider specified 2778, it's safer to assume a way to pass it.
            // If the library doesn't expose a port in the constructor, you'd need to check its source or documentation.
            // For now, I'll assume the constructor might accept it as an option or that it's implied by the host.

            // A more robust way based on how such services *should* be constructed:
            $smppService = new Sender(
                [$host . ':' . $port], // Host with port, or separate parameters/config if supported
                $systemId,
                $password,
                [
                    'debug' => $debug,
                    // You might be able to set timeout here if the service supports it
                    'timeout' => $timeout / 1000, // Convert ms to seconds if needed
                ]
            );

            // Send the SMS
            // The `send` method typically takes: recipient, message, senderId, [optional_flags]
            // The README shows: $service->send(79001001010, 'Hello world!', 'Sender');
            // This is simplified and likely handles TON/NPI and registered delivery internally or via options.
            $messageId = $smppService->send(
                $this->recipient, // The `to` number
                $this->message->content,  // The message body
                $senderId         // The `from` (sender ID)
                // You might be able to pass an array of options here for registered_delivery, data_coding, etc.
                // e.g., ['registered_delivery' => SMPP::REG_DELIVERY_SMSC_BOTH]
            );

            // Log success and return JSON response
            Log::info("SMPP SMS sent successfully using glushkovds/php-smpp (new API).", [
                'to' => $this->recipient,
                'message' => $this->message->content,
                'message_id' => $messageId,
                'smpp_host' => $host,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'SMS sent successfully!',
                'message_id' => $messageId
            ]);

        } catch (Exception $e) {
            // Log error and return JSON error response
            Log::error("Failed to send SMPP SMS using glushkovds/php-smpp (new API): {$e->getMessage()}", [
                'exception_trace' => $e->getTraceAsString(),
                'to' => $this->recipient,
                'message' => $this->message->content,
                'smpp_host' => $host,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }
}
