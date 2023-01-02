# Laravel Elastic Cloud logging for MessageLogged event.

This package will listen to the *Illuminate\Log\Events\MessageLogged* event and queue the log data to be sent to Elastic Kibana index by your queue handler. 

It will listen to all Log events and will also handle exceptions separately.
___
### Requirements
```
- PHP >=8.0
- laravel/framework ^9.0
```

### Dependencies
```json
"elasticsearch/elasticsearch": "^8.5"
```
___

## Important notice

This package implements the queueable job logic.

Laravel also provides before and after functionality for jobs.

You may want to log your jobs running.

Maybe like this: 
```        
Queue::before(function (JobProcessing $event) {
    Log::info('Starting job: ' . $event->job->resolveName() . ' with id ' . $event->job->getJobId());
});
```

That is a bad idea as we are listening to **ALL** MessageLogged events.

It will cause infinite loop of logs in your application. Feel free to create a pull request to solve this.

#### In general be careful about the possibility of log loops.

Tested with redis and database with queues running with ``php artisan queue:work`` or ``queue:listen``. 

If you're working on Google cloud, consider setting up [stackkits laravel-google-cloud-tasks-queue](https://github.com/stackkit/laravel-google-cloud-tasks-queue)

___

## Installation

Install package
```bash
composer require outl1ne/laravel-elastic-logger
```


Add the package service provider to your Laravel app service provider
```bash
# config/app.php
'providers' => [
    ...
    Outl1ne\LaravelElasticLogger\LaravelElasticLoggerServiceProvider::class,
    ...
],
```

Configure the environment variables for the package
```bash
ELASTIC_ENABLED=true # only 'true' value will enable MessageLogged event being listened
ELASTIC_INDEX= # index where to send the logs
ELASTIC_CLOUD_ID= # your elastic instance cloud ID
ELASTIC_API_KEY= # your elastic API key
```

Make sure your [queue handler](https://laravel.com/docs/9.x/queues#driver-prerequisites) is configured properly
___

## Cheat sheet / quick guide to set up Elastic.

Elastic offers 14 day free trial. Following this guide you will be able to configure you application within 10 minutes to test out if this satisfies your needs.

1. [Create an account](https://cloud.elastic.co)
2. Create a new instance.
3. Navigate to Management -> Dev Tools on the burger menu in your new instance.
4. Paste this bare-bones template to make a new index. Important is to predefine the datetime in order for Kibana to know which field to use as a date sort.
```
PUT /your_index_name
{
"settings": {"number_of_shards": 1},"mappings": {"properties": {
"context": {"properties": {"message": {"type": "text","fields": {
"keyword": {"type": "keyword","ignore_above": 256}}}}},
"datetime": {"type": "date","format": "yyyy-MM-dd HH:mm:ss.SSSSSS"},
"level": {"type": "text","fields": {"keyword": {"type": "keyword","ignore_above": 256}}},
"message": {"type": "text","fields": {"keyword": {"type": "keyword","ignore_above": 256}}}}}
}
```
Datetime format is important here as it needs to match what the package sends out.

5. Create a new Kibana Data view. Choose a name and match the index pattern with the index name you created. Kibana allows wildcards. IE: project_* for project_dev and project_prod for viewing both indexes on the same data view.
##### Choose the "datetime" field as the Timestamp field! This is important to allow convenient filtering based on the timestamp.

Elastic is able to create a new index on the fly but you cannot change the datetime field for the timestamp later, which will result in not having a proper timestamp filter.

Save the data view and view your newly created data view in the "Discover" section in the Analytics.

___
## What the future brings
* Log level handling - IE send only errors & debug logs.
* In case Elastic API fails, it is currently not being handled.

___
## License
This project is open-sourced software licensed under the [MIT license](LICENSE.md).
