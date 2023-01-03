<?php

namespace Outl1ne\LaravelElasticLogger\Listeners;

use DateTime;
use DateTimeZone;
use Exception;
use Outl1ne\LaravelElasticLogger\Jobs\HandleSendLogToElastic;
use Illuminate\Log\Events\MessageLogged;

class PrepareLogPayload
{
    /** @var string */
    private string $excludeLogLevels;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->excludeLogLevels = config('elastic_logging.elastic_exclude_levels');
    }

    /**
     * Handle the event.
     *
     * @param MessageLogged $event
     * @return void
     *
     * @throws Exception
     */
    public function handle(MessageLogged $event): void
    {
        try {
            if (in_array($event->level, explode(',', $this->excludeLogLevels))) {
                return;
            }
            $dateTime = new DateTime('NOW', new DateTimeZone('UTC'));
            if (array_key_exists('exception', $event->context)) {
                $exception = $event->context['exception'];
                $logData = [
                    'message' => $event->message,
                    'level' => $event->level,
                    'datetime' => $dateTime,
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
                    'datetime' => $dateTime,
                    'context' => $event->context
                ];
            }
            HandleSendLogToElastic::dispatch($logData);
        } catch (Exception $exception) {
            // TODO - exception handling.
        }
    }
}

