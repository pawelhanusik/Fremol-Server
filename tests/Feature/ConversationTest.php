<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    private $user;
    private $conv;

    protected function setUp() :void {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->conv = Conversation::factory()->for($this->user, 'creator')->create();
        $this->user->addToConversation($this->conv);
    }
    public function testIndex() {
        $this->actingAs($this->user)->getJson('/api/user/conversations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    0 => [
                        'id' => $this->conv->id,
                        'users' => [
                            0 => [
                                'id' => $this->user->id
                            ]
                        ]
                    ]
                ]
            ])->assertJsonCount(1, 'data.0.users')
        ;
    }
    public function testShow() {
        $this->actingAs($this->user)->getJson('/api/user/conversations/' . $this->conv->id)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $this->conv->id,
                    'users' => [
                        0 => [
                            'id' => $this->user->id
                        ]
                    ]
                ]
            ])->assertJsonCount(1, 'data.users')
        ;
    }
    public function testStore() {
        $this->actingAs($this->user)->postJson('/api/user/conversations', [
            'name' => 'Test Conversation',
            'participants' => []
        ])->assertOk()
            ->assertJson([
                'message' => 'Conversation created'
            ])
            ->assertJsonStructure([
                'conversation'
            ])
        ;
    }
    public function testUpdate() {
        $this->actingAs($this->user)->putJson('/api/user/conversations/' . $this->conv->id, [
            'name' => 'changed test conv',
            'participants' => []
        ])->assertOk();

        $this->actingAs($this->user)->getJson('/api/user/conversations/' . $this->conv->id)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $this->conv->id,
                    'name' => 'changed test conv'
                ]
            ])
        ;
    }
    public function testDestroy() {
        $this->actingAs($this->user)->getJson('/api/user/conversations/' . $this->conv->id)
            ->assertStatus(200);
        $this->actingAs($this->user)->deleteJson('/api/user/conversations/' . $this->conv->id)
            ->assertOk();
        $this->actingAs($this->user)->getJson('/api/user/conversations/' . $this->conv->id)
            ->assertStatus(404);
    }
    public function testLeave() {
        $user2 = User::factory()->create();
        $user2->addToConversation($this->conv);

        $this->actingAs($this->user)->getJson('/api/user/conversations/' . $this->conv->id)
            ->assertOk()
            ->assertJsonCount(2, 'data.users');

        $this->actingAs($user2)->deleteJson('/api/user/conversations/' . $this->conv->id . '/leave')
            ->assertOk();

        $this->actingAs($this->user)->getJson('/api/user/conversations/' . $this->conv->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.users');

        $this->actingAs($this->user)->deleteJson('/api/user/conversations/' . $this->conv->id . '/leave')
            ->assertStatus(403);
        
        $this->actingAs($this->user)->getJson('/api/user/conversations/' . $this->conv->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.users');
    }
}
