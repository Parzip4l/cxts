<?php

return [
    'enabled' => (bool) env('FIREBASE_ENABLED', false),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'credentials_path' => env('FIREBASE_CREDENTIALS_PATH'),
    'android_channel_id' => env('FIREBASE_ANDROID_CHANNEL_ID', 'engineering_ops_channel'),
];

