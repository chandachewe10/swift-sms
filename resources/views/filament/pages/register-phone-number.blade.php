<x-filament-panels::page>
    <div class="space-y-6">
        @if ($config)
            <div class="rounded-xl border border-green-200 bg-green-50 p-6 dark:border-green-800 dark:bg-green-950">
                <h3 class="text-lg font-semibold text-green-900 dark:text-green-100">
                    WhatsApp Number Registered
                </h3>
                <dl class="mt-4 grid gap-3 text-sm text-green-900 dark:text-green-100 sm:grid-cols-2">
                    <div>
                        <dt class="font-medium">Business Phone</dt>
                        <dd>{{ $config->phone_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">Business ID</dt>
                        <dd>{{ $config->business_id ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        @else
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-950">
                <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100">
                    No WhatsApp number registered yet
                </h3>
                <p class="mt-2 text-sm text-amber-800 dark:text-amber-200">
                    Register your company WhatsApp number through Meta. After authentication, 
                    Meta will send a code to our system and your company config will be saved automatically.
                </p>
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Meta Embedded Signup
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Click the button below to authenticate with Meta and connect your WhatsApp Business phone number.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                {{-- Option 1: Use Filament's built-in button (always styled correctly) --}}
                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Pages\RegisterPhoneNumberPage::getOnboardUrl() }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    color="success"
                    size="lg"
                    icon="heroicon-o-device-phone-mobile"
                >
                    Register WhatsApp Phone Number
                </x-filament::button>
            </div>

            <p id="signup-status" class="mt-4 hidden text-sm"></p>
        </div>
    </div>

    <script>
        // ... your existing script unchanged
    </script>
</x-filament-panels::page>