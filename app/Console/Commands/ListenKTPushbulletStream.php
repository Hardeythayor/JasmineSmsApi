<?php

namespace App\Console\Commands;

use Amp\Websocket\Client\WebsocketHandshake;
use Illuminate\Console\Command;

use function Amp\async;
use function Amp\Websocket\Client\connect;
use Amp\Future;
use App\Models\MessageRecipient;
use App\Models\SmsMessage;
use App\Models\ThirdPartyNumber;
use Illuminate\Support\Facades\Log;

class ListenKTPushbulletStream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ktpushbullet:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connects to KT Pushbullet WebSocket stream and listens for messages.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = config("pushbullet.keys.kt");//$this->argument('apiKey');
        $websocketUrl = 'wss://stream.pushbullet.com/websocket/' . $apiKey;

        $this->info("connecting to: " . $websocketUrl);
        $connection = connect($websocketUrl);
        $this->info("Establishing connection");

        foreach ($connection as $message) {
            $this->info("connection received");
            // $message is an instance of Amp\Websocket\WebsocketMessage
            $payload = $message->buffer();

            try {
                $data = json_decode($payload, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Call the data handler.
                    // If handlePushbulletData also needs to be async, you'd yield it.
                    $this->handlePushbulletData($data);
                } else {
                    $this->warn("Received non-JSON message or malformed JSON: " . $payload);
                }
            } catch (\Throwable $e) {
                $this->error("Error processing message: " . $e->getMessage());
            }

            // $data = json_decode($payload, true);

            if ($payload === '100') {
                $connection->close();
                break;
            }
        }
    }

    /**
     * Handle the decoded Pushbullet data.
     *
     * @param array $data
     * @return void
     */
    protected function handlePushbulletData(array $data)
    {
        // This is where you put your logic to extract and process data
        // The structure of Pushbullet's WebSocket messages varies based on the "type" of event.
        // You'll need to consult Pushbullet's API documentation for exact message formats.

        // Example: Log the type and body of a push
        // Log::info(['data' => $data, 'dataType' => $data['type'], 'dataPush' => $data['push'],]);
        if (isset($data['type'])) {
            $this->line("Data Type: " . $data['type']);

            if ($data['type'] === 'tickle' && isset($data['subtype'])) {
                $this->line("  Subtype: " . $data['subtype']);
                // A 'tickle' means new data is available, you might then use Pushbullet's REST API
                // to fetch the actual new pushes, notes, etc.
                // For example, if subtype is 'push', you might call the Pushbullet /v2/pushes API.
                $this->info("  Received a 'tickle' - consider fetching new data via REST API.");
            } elseif ($data['type'] === 'push' && isset($data['push'])) {
                $push = $data['push'];
                if($push['type'] === 'sms_changed') {
                    $title = $push['notifications'][0]['title'];
                    $body = $push['notifications'][0]['body'];
                    $device_id = $push['source_device_iden'];

                    
                    // $this->line("  Push Type: " . ($push['type'] ?? 'unknown'));
                    // $this->line("  Push Title: " . ($title?? 'N/A'));
                    // $this->line("  Push Body: " . ($body ?? 'N/A'));

                    $formatted_number = formatPhoneNumber($title);
                    $recipient = ThirdPartyNumber::where('label', 'kt')->first()?->phone;
                    // Log::info(['PUSH_body' => $title, 'PUSH_title' => $body, 'device_identity' => $device_id, 'number' => $formatted_number]);

                    $sms_message = SmsMessage::where('source' , $formatted_number)->first();
                  
                   if($sms_message) {
                        MessageRecipient::where(['message_id' => $sms_message->id, 'transformed_phone' => $recipient])->update([
                            'status' => 'completed',
                            'phone_sms_id' => $device_id,
                        ]);
                    }
                    $this->info("  Push data processed and potentially stored.");
                }
            } else {
                // Log the full data for other types to inspect
                $this->line("  Full data for type '{$data['type']}': " . json_encode($data, JSON_PRETTY_PRINT));
            }
        } else {
            $this->warn("Received message with no 'type' field: " . json_encode($data));
        }
    }
}
