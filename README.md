# Laravel Elastic Cloud logging for MessageLogged event.

This package will listen to the *Illuminate\Log\Events\MessageLogged* event and queue the log data to be sent to Elastic Kibana index by your queue handler. 

It will listen to all Log events and will also handle exceptions separately.
___
### Requirements
```
- PHP >=8.0
- laravel/framework ^8.7 || ^9.0
```

### Dependencies
```json
"elasticsearch/elasticsearch": "^8.5"
```
___

## Important notice

Do not log Queue::before / after events. This will cause an infinite loop of logging and will cause your application to crash.

#### In general be careful about the possibility of log loops.

Tested with redis and database queue drivers. 

If you're working on Google cloud, consider setting up [stackkits laravel-google-cloud-tasks-queue](https://github.com/stackkit/laravel-google-cloud-tasks-queue)

___

## Installation

Install package
```bash
composer require outl1ne/laravel-elastic-logger
```

Configure the environment variables for the package
```bash
ELASTIC_ENABLED=true # only 'true' value will enable MessageLogged event being listened
ELASTIC_INDEX= # index where to send the logs
ELASTIC_CLOUD_ID= # your elastic instance cloud ID
ELASTIC_API_KEY= # your elastic API key
ELASTIC_EXCLUDE_LOG_LEVELS= # comma separated log levels to be excluded, IE: "info,warning"
```

Make sure your [queue handler](https://laravel.com/docs/9.x/queues#driver-prerequisites) is configured properly
___

## Cheat sheet / quick guide to set up Elastic.

Elastic offers 14 day free trial.

This package will auto-generate the indexes for you based on the ELASTIC_INDEX value.
It will create indexes in the pattern of: `ELASTIC_INDEX + _ + d-m-Y`

1. [Create an account](https://cloud.elastic.co)
2. Create a new instance.
3. Create a new Kibana Data view. Choose a name and match the index pattern with the index name you created. Kibana allows wildcards.

##### Choose the "datetime" field as the Timestamp field! This is important to allow convenient filtering based on the timestamp.

Elastic is able to create a new index on the fly but you cannot change the datetime field for the timestamp later, which will result in not having a proper timestamp filter.

Save the data view and view your newly created data view in the "Discover" section in the Analytics.

___
## What the future brings
* lifecycle policy management

___
## License
This project is open-sourced software licensed under the [MIT license](LICENSE.md).