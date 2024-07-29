<?php

namespace Tests\StarterKits\Filament\Resources\Contacts;

use App\Api\StarterKits\Filament\Resources\Contacts\Presenters\Contacts\Data\ContactData as DataResponse;
use App\Models\Contact as Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ContactResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Collection $contacts;

    /**
     * Setup the test
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->contacts = Contact::factory(10)->create();
    }

    /**
     * Test to show a single contact
     *
     * @return void
     */
    public function test_can_show_a_contact()
    {
        $contact = $this->contacts->random();

        $response = $this->json(
            method: 'GET',
            uri: route('api.v1.filament.contacts.show', $contact->id),
            headers: [
                'x-rest-presenter-api-key' => config('rest-presenter.auth.key'),
                'x-rest-presenter' => 'Contact'
            ],
        );

        $response->assertStatus(200);

        $this->assertEquals(
            expected: DataResponse::from($contact)->toArray(),
            actual: $response->json(),
            message: 'Response data is in the expected format',
        );
    }

    /**
     * Test to list all contacts
     *
     * @return void
     */
    public function test_can_list_all_contacts()
    {
        $response = $this->json(
            method: 'GET',
            uri: route('api.v1.filament.contacts.index'),
            headers: [
                'x-rest-presenter-api-key' => config('rest-presenter.auth.key'),
                'x-rest-presenter' => 'Contact'
            ],
        );

        $response->assertStatus(200);

        $this->assertEquals(
            expected: DataResponse::collect($this->contacts)->toArray(),
            actual: $response->json(),
            message: 'Response data is in the expected format',
        );
    }
}
