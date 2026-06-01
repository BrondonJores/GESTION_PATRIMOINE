<?php

namespace App\Services;

use App\Models\AppSetting;

class ContactSettingService
{
    private const SETTING_KEY = 'contact.settings';

    private const DEFAULT_SETTINGS = [
        'email_emetteur' => 'houakeukamguiabrandonjores@gmail.com',
        'nom_emetteur' => 'Gestion Patrimoine IFTTS',
        'telephone_emetteur' => '',
        'twilio_sid' => '',
        'twilio_token' => '',
        'twilio_number' => '',
    ];

    public function getSettings(): array
    {
        $settings = AppSetting::where('key', self::SETTING_KEY)->value('value');

        if (!is_array($settings)) {
            return self::DEFAULT_SETTINGS;
        }

        return array_merge(self::DEFAULT_SETTINGS, $settings);
    }

    public function saveSettings(array $data): void
    {
        AppSetting::updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => $data]
        );
    }
}
