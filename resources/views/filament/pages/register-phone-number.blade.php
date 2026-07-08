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
                    @if ($config->waba_id)
                        <div>
                            <dt class="font-medium">WABA ID</dt>
                            <dd>{{ $config->waba_id }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @else
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-950">
                <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100">
                    No WhatsApp number registered yet
                </h3>
                <p class="mt-2 text-sm text-amber-800 dark:text-amber-200">
                    Register your company WhatsApp number through Meta. After authentication,
                    Meta will send a confirmation to our system and your company config will be saved automatically.
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
                <x-filament::button
                    id="whatsapp-signup-btn"
                    color="success"
                    size="lg"
                    icon="heroicon-o-device-phone-mobile"
                >
                    Connect WhatsApp
                </x-filament::button>
            </div>

            <p id="signup-status" class="mt-4 hidden text-sm font-medium"></p>
        </div>
    </div>

    <script>
    (function () {
        const btn = document.getElementById('whatsapp-signup-btn');
        const status = document.getElementById('signup-status');

        function showStatus(message, isError) {
            status.textContent = message;
            status.className = 'mt-4 text-sm font-medium ' + (isError ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400');
            status.classList.remove('hidden');
        }

        function getMetaToken() {
            // Retrieve the Sanctum token stored by Filament/Jetstream
            const sanctum = document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='));
            return null; // token is sent via cookie automatically for same-origin requests
        }

        function apiFetch(url, options) {
            return fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(
                        (document.cookie.match(/XSRF-TOKEN=([^;]+)/) || [])[1] || ''
                    ),
                    ...(options.headers || {}),
                },
                credentials: 'same-origin',
            });
        }

        async function startOnboarding() {
            btn.disabled = true;
            showStatus('Preparing your onboarding session…', false);

            let onboardUrl;

            try {
                const res = await apiFetch('/api/whatsapp/start-onboarding', { method: 'POST' });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Failed to start onboarding.');
                }

                onboardUrl = data.onboard_url;
            } catch (err) {
                showStatus('Could not start onboarding: ' + err.message, true);
                btn.disabled = false;
                return;
            }

            showStatus('Opening Meta signup…', false);

            // Open Meta Embedded Signup in a centred popup
            const width = 600, height = 700;
            const left = Math.round(window.screenX + (window.outerWidth - width) / 2);
            const top = Math.round(window.screenY + (window.outerHeight - height) / 2);
            const popup = window.open(
                onboardUrl,
                'MetaEmbeddedSignup',
                `width=${width},height=${height},top=${top},left=${left},scrollbars=yes`
            );

            // Listen for the postMessage callback from Meta
            const messageHandler = async function (event) {
                // Accept messages from facebook.com domains only
                if (!/facebook\.com$/.test(event.origin)) return;

                const msg = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;

                if (msg.type !== 'WA_EMBEDDED_SIGNUP') return;

                window.removeEventListener('message', messageHandler);

                if (msg.event === 'FINISH') {
                    await handleSignupFinished(msg.data);
                } else if (msg.event === 'CANCEL') {
                    showStatus('Signup cancelled.', true);
                    btn.disabled = false;
                } else if (msg.event === 'ERROR') {
                    showStatus('Meta returned an error: ' + (msg.data?.error_message || 'Unknown error'), true);
                    btn.disabled = false;
                }
            };

            window.addEventListener('message', messageHandler);

            // Poll for popup close (user navigated away without finishing)
            const pollTimer = setInterval(function () {
                if (!popup || popup.closed) {
                    clearInterval(pollTimer);
                    window.removeEventListener('message', messageHandler);
                    // The PARTNER_APP_INSTALLED webhook will handle completion server-side.
                    showStatus(
                        'Popup closed. Your WhatsApp connection will appear here once Meta confirms the setup.',
                        false
                    );
                    btn.disabled = false;
                }
            }, 1000);
        }

        async function handleSignupFinished(data) {
            /*
             * data from Meta may contain:
             *   phone_number_id, waba_id, business_id, phone_number, code, etc.
             *
             * The authorization code (data.code) must be exchanged server-side.
             */
            showStatus('Finalising your registration…', false);

            const payload = {
                code: data.code,
                phone_number_id: data.phone_number_id || null,
                waba_id: data.waba_id || null,
                business_account_id: data.business_account_id || data.waba_id || null,
                business_id: data.business_id || null,
                phone_number: data.phone_number || null,
                raw_payload: data,
            };

            try {
                const res = await apiFetch('/api/meta_embedded_signup', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                const result = await res.json();

                if (res.ok && result.success) {
                    showStatus('WhatsApp connected successfully! Refreshing…', false);
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showStatus('Registration error: ' + (result.message || 'Please try again.'), true);
                    btn.disabled = false;
                }
            } catch (err) {
                showStatus('Network error: ' + err.message, true);
                btn.disabled = false;
            }
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            startOnboarding();
        });
    })();
    </script>
</x-filament-panels::page>
