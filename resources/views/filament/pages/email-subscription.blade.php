<x-filament-panels::page>
    <div class="flex justify-center py-8">
        <div class="w-full max-w-lg">

            {{-- Header --}}
            <div class="mb-8 text-center">
                <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-9 w-9 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Bulk Email Messaging</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Send professional emails directly to individuals or all your contacts at once</p>
            </div>

            {{-- Pricing card --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                {{-- Price --}}
                <div class="mb-6 text-center">
                    <span class="text-4xl font-extrabold text-gray-900 dark:text-white">K500</span>
                    <span class="text-gray-500 dark:text-gray-400"> / month</span>
                    <div class="mt-2">
                        <span class="inline-block rounded-full bg-blue-100 px-3 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            🎉 10 free sends included on sign-up
                        </span>
                    </div>
                </div>

                {{-- Features --}}
                <ul class="mb-8 space-y-3">
                    @foreach ([
                        'Send single emails to any address',
                        'Bulk send to all contacts with email addresses',
                        'Rich HTML email composer',
                        'Use your own SMTP server (Gmail, Zoho, etc.)',
                        'Full delivery history & logs',
                    ] as $feature)
                        <li class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="h-5 w-5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                {{-- CTA --}}
                <a href="{{ route('subscription.email') }}"
                   class="block w-full rounded-lg bg-blue-600 px-6 py-3 text-center text-base font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    Subscribe Now — K500/month
                </a>

                <p class="mt-4 text-center text-xs text-gray-400">
                    Payment processed securely via Lenco. Your access is activated instantly after payment.
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
