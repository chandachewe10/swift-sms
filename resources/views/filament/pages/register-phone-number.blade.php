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
        const ONBOARD_URL = @json($onboardUrl);
        const CSRF_TOKEN  = @json(csrf_token());
        const IS_POPUP    = window.opener && window.opener !== window;

        // -----------------------------------------------------------------------
        // POPUP CHILD mode: this page loaded inside the Meta OAuth popup after
        // Meta redirected back to our redirect_uri with ?code=...
        // Send the code to the parent window and close.
        // -----------------------------------------------------------------------
        if (IS_POPUP) {
            const params = new URLSearchParams(window.location.search);
            const code   = params.get('code');

            if (code) {
                window.opener.postMessage({
                    type:             'WA_EMBEDDED_SIGNUP',
                    event:            'FINISH',
                    data: {
                        code:             code,
                        state:            params.get('state')           || null,
                        waba_id:          params.get('waba_id')         || null,
                        business_id:      params.get('business_id')     || null,
                        phone_number_id:  params.get('phone_number_id') || null,
                        phone_number:     params.get('phone_number')    || null,
                    },
                }, window.location.origin);
            } else {
                // Meta may have returned an error or the user cancelled
                window.opener.postMessage({
                    type:  'WA_EMBEDDED_SIGNUP',
                    event: params.get('error') ? 'ERROR' : 'CANCEL',
                    data:  { error_message: params.get('error_description') || '' },
                }, window.location.origin);
            }

            window.close();
            return; // stop the rest of the script from running in popup mode
        }

        // -----------------------------------------------------------------------
        // PARENT mode: normal Filament page
        // -----------------------------------------------------------------------
        const btn    = document.getElementById('whatsapp-signup-btn');
        const status = document.getElementById('signup-status');

        if (! btn) return; // page already shows connected state, nothing to do

        function showStatus(message, isError) {
            status.textContent = message;
            status.className   = 'mt-4 text-sm font-medium ' +
                (isError ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400');
            status.classList.remove('hidden');
        }

        function apiFetch(url, options) {
            return fetch(url, {
                ...options,
                headers: {
                    'Accept':       'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    ...(options.headers || {}),
                },
                credentials: 'same-origin',
            });
        }

        function openSignupPopup() {
            btn.disabled = true;
            showStatus('Opening Meta signup…', false);

            const width  = 600, height = 700;
            const left   = Math.round(window.screenX + (window.outerWidth  - width)  / 2);
            const top    = Math.round(window.screenY + (window.outerHeight - height) / 2);

            const popup = window.open(
                ONBOARD_URL,
                'MetaEmbeddedSignup',
                `width=${width},height=${height},top=${top},left=${left},scrollbars=yes`
            );

            // Listen for postMessage from Meta OR from our own popup child page
            const messageHandler = async function (event) {
                // Accept messages from Meta's domain OR our own origin (popup redirect)
                const isMeta = /facebook\.com$/.test(event.origin);
                const isSelf = event.origin === window.location.origin;
                if (! isMeta && ! isSelf) return;

                let msg;
                try {
                    msg = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
                } catch (e) { return; }

                if (msg.type !== 'WA_EMBEDDED_SIGNUP') return;

                window.removeEventListener('message', messageHandler);
                clearInterval(pollTimer);

                if (msg.event === 'FINISH') {
                    await handleSignupFinished(msg.data);
                } else if (msg.event === 'CANCEL') {
                    showStatus('Signup cancelled. You can try again.', true);
                    btn.disabled = false;
                } else if (msg.event === 'ERROR') {
                    showStatus('Meta returned an error: ' + (msg.data?.error_message || 'Unknown error'), true);
                    btn.disabled = false;
                }
            };

            window.addEventListener('message', messageHandler);

            const pollTimer = setInterval(function () {
                if (! popup || popup.closed) {
                    clearInterval(pollTimer);
                    window.removeEventListener('message', messageHandler);
                    showStatus(
                        'Popup closed. Your WhatsApp connection will appear here once Meta confirms the setup.',
                        false
                    );
                    btn.disabled = false;
                }
            }, 1000);
        }

        async function handleSignupFinished(data) {
            showStatus('Finalising your registration…', false);

            const payload = {
                code:                data.code                || null,
                state:               data.state               || null,
                phone_number_id:     data.phone_number_id     || null,
                waba_id:             data.waba_id             || null,
                business_account_id: data.business_account_id || data.waba_id || null,
                business_id:         data.business_id         || null,
                phone_number:        data.phone_number        || null,
                raw_payload:         data,
            };

            try {
                const res    = await apiFetch('/api/meta_embedded_signup', {
                    method: 'POST',
                    body:   JSON.stringify(payload),
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
            openSignupPopup();
        });
    })();
    </script>
</x-filament-panels::page>
