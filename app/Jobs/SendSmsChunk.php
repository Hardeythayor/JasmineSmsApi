<?php

namespace App\Jobs;

use App\Models\SmsMessage;
use App\Services\EasySmsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $recipients; // This will be an array of phone numbers (e.g., up to 30)
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(SmsMessage $message, array $recipients)
    {
        $this->recipients = $recipients;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $eims = new EasySmsGateway;
            $eims->sendChunkedSMS($this->message, $this->recipients);

            Log::info("SMS chunk sent to: " . implode(', ', $this->recipients));
        } catch (\Exception $e) {
            Log::error("Failed to send SMS chunk to recipients: " . implode(', ', $this->recipients) . ". Error: " . $e->getMessage());
            // You might want to implement specific retry logic here,
            // or mark individual numbers as failed if the service provides granular feedback.
        }
    }
}
