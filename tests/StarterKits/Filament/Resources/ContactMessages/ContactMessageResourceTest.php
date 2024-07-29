<?php

namespace Tests\StarterKits\Filament\Resources\ContactMessages;

use App\Api\StarterKits\Filament\Resources\ContactMessages\Presenters\Messages\Data\MessagesData as DataResponse;
use App\Models\Messages as Messages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ContactMessageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Collection $messages;

    /**
     * Setup the test
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->messages = Messages::factory(10)->create();
    }

    /**
     * Test to show a single messages
     *
     * @return void
     */
    public function test_can_show_a_messages()
    {
        $messages = $this->messages->random();

        $response = $this->json(
            method: 'GET',
            uri: route('api.v1.filament.messages.show', $messages->id),
            headers: [
                'x-rest-presenter-api-key' => config('rest-presenter.auth.key'),
                'x-rest-presenter' => 'Messages'
            ],
        );

        $response->assertStatus(200);

        $this->assertEquals(
            expected: DataResponse::from($messages)->toArray(),
            actual: $response->json(),
            message: 'Response data is in the expected format',
        );
    }

    /**
     * Test to list all messages
     *
     * @return void
     */
    public function test_can_list_all_messages()
    {
        $response = $this->json(
            method: 'GET',
            uri: route('api.v1.filament.messages.index'),
            headers: [
                'x-rest-presenter-api-key' => config('rest-presenter.auth.key'),
                'x-rest-presenter' => 'Messages'
            ],
        );

        $response->assertStatus(200);

        $this->assertEquals(
            expected: DataResponse::collect($this->messages)->toArray(),
            actual: $response->json(),
            message: 'Response data is in the expected format',
        );
    }
}
