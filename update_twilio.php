<?php
use App\Services\ContactSettingService;

$service = app(ContactSettingService::class);
$settings = $service->getSettings();
$settings['twilio_sid'] = 'USae6a306dcefacf72b764174bd01387b8';
$settings['twilio_token'] = '6c712fcde67357e3064eb5e0ced4f23e';
$service->saveSettings($settings);
print_r($service->getSettings());
