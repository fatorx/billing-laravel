<?php

namespace App\Console\Commands;

use App\External\QueueConnection;
use App\Services\FileProcessingService;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumeFile extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected string $queueName = 'process_files';
    protected $description = 'Consume files';

    protected FileProcessingService $fileProcessingService;

    public function __construct(FileProcessingService $fileProcessingService)
    {
        parent::__construct();
        $this->fileProcessingService = $fileProcessingService;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $connection = (new QueueConnection)->get();
        $channel = $connection->channel();
        $channel->queue_declare(
            $this->queueName, false, true, false, false
        );

        $callback = function (AMQPMessage $msg) {
            $this->info('Received: ' . $msg->body);

            try {
                $this->fileProcessingService->processMessage($msg->body);
                $msg->ack();
            } catch (Exception $e) {
                $this->error('Error processing message: ' . $e->getMessage());
                // Optionally, you can reject the message and requeue it
                $msg->nack(false, true);
            }
        };

        $channel->basic_consume(
            $this->queueName, '', false,
            false, false, false, $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
