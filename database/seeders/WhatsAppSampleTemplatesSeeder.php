<?php

namespace Database\Seeders;

use App\Models\WhatsAppTemplate;
use Illuminate\Database\Seeder;

class WhatsAppSampleTemplatesSeeder extends Seeder
{
    /**
     * Meta pre-approved sample templates.
     * These are seeded for the super_admin account (user_id = 1)
     * and marked APPROVED so they are immediately usable.
     */
    public function run(): void
    {
        $templates = [
            [
                'name'             => 'opening_our_business_time',
                'category'         => 'UTILITY',
                'language'         => 'en_US',
                'parameter_format' => 'positional',
                'status'           => 'APPROVED',
                'body_text'        => 'Our business is now open. Thank you for choosing us.',
            ],
            [
                'name'             => 'system_maintenance',
                'category'         => 'UTILITY',
                'language'         => 'en_US',
                'parameter_format' => 'positional',
                'status'           => 'APPROVED',
                'body_text'        => 'We are performing scheduled system maintenance. We apologize for any inconvenience.',
            ],
            [
                'name'             => 'auto_pay_reminder_2',
                'category'         => 'UTILITY',
                'language'         => 'en_US',
                'parameter_format' => 'positional',
                'status'           => 'APPROVED',
                'body_text'        => "Hi {{1}}, this is to remind you of your upcoming auto-pay:\n\nDate: {{2}}\nAccount: {{3}}\nAmount: {{4}}\n\nThank you and have a nice day.",
            ],
        ];

        foreach ($templates as $tpl) {
            WhatsAppTemplate::firstOrCreate(
                ['name' => $tpl['name'], 'user_id' => 1],
                array_merge($tpl, ['user_id' => 1])
            );
        }

        $this->command->info('WhatsApp sample templates seeded.');
    }
}
