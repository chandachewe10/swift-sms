<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($this->getCards() as $card)
        
            <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
                <a href="{{ $card->getUrl() }}" class="block text-center">
                    <div class="text-lg font-semibold">{{ $card->getLabel() }}</div>
                    <div class="text-gray-600">{{ $card->getDescription() }}</div>
                    <div class="text-gray-600">{{ $card->getValue() }}</div>
                    <div class="mt-4 text-center">
                        <x-filament::icon :name="$card->getDescriptionIcon()" class="h-6 w-6 mx-auto" />
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
