<?php

return [
    'elastic_cloud_id' => env('ELASTIC_CLOUD_ID', ''),
    'elastic_api_key' => env('ELASTIC_API_KEY', ''),
    'elastic_index' => env('ELASTIC_INDEX', ''),
    'elastic_enabled' => env('ELASTIC_ENABLED', false),
    'elastic_exclude_levels' => env('ELASTIC_EXCLUDE_LOG_LEVELS', ''),
    'elastic_lifecycle_policy' => env('ELASTIC_LIFECYCLE_POLICY'),
];
