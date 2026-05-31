<x-filament-panels::page>
    <div class="flex justify-center py-8">
        <div class="w-full max-w-lg">

            {{-- Header --}}
            <div class="mb-8 text-center">
                <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-9 w-9 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">WhatsApp Business Messaging</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Send template messages to your customers via WhatsApp</p>
            </div>

            {{-- Pricing card --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                {{-- Price --}}
                <div class="mb-6 text-center">
                    <span class="text-4xl font-extrabold text-gray-900 dark:text-white">K500</span>
                    <span class="text-gray-500 dark:text-gray-400"> / month</span>
                    <div class="mt-1">
                        <span class="inline-block rounded-full bg-green-100 px-3 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900 dark:text-green-300">
                            🔒 Premium Feature
                        </span>
                    </div>
                </div>

                {{-- Features --}}
                <ul class="mb-8 space-y-3">
                    @foreach ([
                        'Send messages via Meta WhatsApp Cloud API',
                        'Create & manage approved message templates',
                        'Bulk send to multiple recipients',
                        'Real-time delivery status tracking',
                        'Full message history & logs',
                    ] as $feature)
                        <li class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="h-5 w-5 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                {{-- CTA --}}
                <a href="{{ route('subscription.whatsapp') }}"
                   class="block w-full rounded-lg bg-green-600 px-6 py-3 text-center text-base font-semibold text-white shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    Subscribe Now — K500/month
                </a>

                <p class="mt-4 text-center text-xs text-gray-400">
                    Payment processed securely via Lenco. Your access is activated instantly after payment.
                </p>
            </div>

        </div>
    </div>
</x-filament-panels::page>
