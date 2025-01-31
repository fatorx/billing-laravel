<?php

namespace App\Console\Commands;

use App\External\QueueConnection;
use App\Services\FileProcessingService;
use App\Logs\Log;
use App\Services\LineProcessingService;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class ConsumeMail extends Command
{
    use Log;

    protected $signature = 'rabbitmq:consume-mail';
    protected string $queueName = 'process_line';
    protected $description = 'Consume Line';

    protected LineProcessingService $lineProcessingService;

    public function __construct(LineProcessingService $lineProcessingService)
    {
        parent::__construct();
        $this->lineProcessingService = $lineProcessingService;
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
            $this->info('Received: ' . $msg->body . "\n");

            try {
                $this->lineProcessingService->processMessage($msg->body);
                $msg->ack();
            } catch (Exception $e) {
                $this->error('Error processing message: ' . $e->getMessage());
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
