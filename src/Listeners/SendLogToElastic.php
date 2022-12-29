<?php

namespace Outl1ne\LaravelElasticLogger\Listeners;

use Outl1ne\LaravelElasticLogger\Jobs\HandleSendLogToElastic;
use Illuminate\Log\Events\MessageLogged;

class SendLogToElastic
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param MessageLogged $event
     * @return void
     */
    public function handle(MessageLogged $event): void
    {
        if (array_key_exists('exception', $event->context)) {
            $exception = $event->context['exception'];
            $logData = [
                'message' => $event->message,
                'level' => $event->level,
                'context' => [
                    'exception_type' => get_class($exception),
                    'exception_message' => $exception->getMessage(),
                    'exception_file' => $exception->getFile(),
                    'exception_line' => $exception->getLine()
                ]
            ];
        } else {
            $logData = [
                'message' => $event->message,
                'level' => $event->level,
                'context' => $event->context
            ];
        }
        HandleSendLogToElastic::dispatch($logData);
    }
}

