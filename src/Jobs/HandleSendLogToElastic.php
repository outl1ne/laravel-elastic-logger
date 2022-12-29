<?php

namespace Outl1ne\LaravelElasticLogger\Jobs;

use DateTime;
use Elastic\Elasticsearch\ClientBuilder;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class HandleSendLogToElastic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array */
    private array $data;

    /** @var string */
    private string $elasticId;

    /** @var string */
    private string $elasticApiKey;

    /** @var string */
    private string $elasticIndex;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->elasticId = config('elastic_logging.elastic_cloud_id');
        $this->elasticApiKey = config('elastic_logging.elastic_api_key');
        $this->elasticIndex = config('elastic_logging.elastic_index');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $params = [
            'index' => $this->elasticIndex,
            'body' => [
                'level' => $this->data['level'],
                'message' => $this->data['message'],
                'context' => $this->data['context'],
                'datetime' => $this->data['dateTime']->format('Y-m-d H:i:s.u')
            ],
        ];
        try {
            $this->getClient()->index($params);
        } catch (AuthenticationException|ServerResponseException|MissingParameterException|ClientResponseException|Exception $e) {
            // TODO - can't report as it will go into an infinite loop ::withoutEvents? maybe email
        }
    }

    /**
     * @throws AuthenticationException
     */
    private function getClient(): Client
    {
        return ClientBuilder::create()
            ->setElasticCloudId($this->elasticId)
            ->setApiKey($this->elasticApiKey)
            ->build();
    }
}
