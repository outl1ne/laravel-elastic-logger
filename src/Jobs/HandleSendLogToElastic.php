<?php

namespace Outl1ne\LaravelElasticLogger\Jobs;

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
use Illuminate\Support\Facades\Cache;

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

    /** @var string */
    private string $lifecyclePolicy;

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
        $this->lifecyclePolicy = config('elastic_logging.elastic_lifecycle_policy');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $indexName = $this->elasticIndex . '_' . $this->data['datetime']->format('d-m-Y');
        $currentIndexNameCached = Cache::get('elastic_index_name');
        if($currentIndexNameCached !== $indexName){
            $this->createIndex($indexName);
        }

        try {
            $this->getClient()->index([
                'index' => $indexName,
                'body' => [
                    'level' => $this->data['level'],
                    'message' => $this->data['message'],
                    'context' => $this->data['context'],
                    'datetime' => $this->data['datetime']->format('Y-m-d H:i:s.u')
                ],
            ]);
        } catch (AuthenticationException|ServerResponseException|MissingParameterException|ClientResponseException|Exception $e) {
            // Do nothing
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

    private function createIndex(string $indexName): void
    {
        try {
            $params = ['index' => $indexName];
            // check if the index is already created, if we get 404, its not.
            $this->getClient()->indices()->getSettings($params);
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                $params = [
                    'index' => $indexName,
                    'body' => [
                        'settings' => [
                            'number_of_shards' => 1
                        ],
                        'mappings' => [
                            'properties' => [
                                'level' => [
                                    'type' => 'keyword'
                                ],
                                'message' => [
                                    'type' => 'text'
                                ],
                                'context' => [
                                    'type' => 'object'
                                ],
                                'datetime' => [
                                    'type' => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss.SSSSSS'
                                ]
                            ]
                        ]
                    ]
                ];
                if($this->lifecyclePolicy){
                    $params['body']['settings']['index.lifecycle.name'] = $this->lifecyclePolicy;
                }
                $response = $this->getClient()->indices()->create($params);
                if($response->getStatusCode() === 200){
                    Cache::put('current_elastic_index', $indexName, 86400);
                }
            }
        } catch (AuthenticationException|ServerResponseException $e) {

        }
    }
}
