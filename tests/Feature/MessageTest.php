<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Tests\TestCase;

class MessageTest extends TestCase
{
    private $user;
    private $conv;
    private $messages;

    protected function setUp() :void {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->conv = Conversation::factory()->for($this->user, 'creator')->create();
        $this->user->addToConversation($this->conv);

        for($i = 0; $i < 10; ++$i) {
            $this->messages[] = Message::factory()
                ->for( $this->user )
                ->for( $this->conv )
                ->create();
        }
    }

    public function testIndexAll() {
        $response = $this->actingAs($this->user)->getJson('/api/conversations/' . $this->conv->id . '/messages')
            ->assertOk()
            ->assertJsonCount(count($this->messages), 'messages')
            ->assertJson([
                'count' => count($this->messages)
            ]);
        $responseMessages = $response->json('messages');
        $this->assertEquals(count($this->messages), count($responseMessages));

        for ($i = 0; $i < count($this->messages); ++$i) {
            $this->assertEquals(
                $this->messages[$i]->id,
                $responseMessages[$i]['id']
            );
        }
    }
    public function testIndexCount() {
        $count = random_int(0, count($this->messages) - 1);
        $responseMessages = $this->actingAs($this->user)->getJson(
            '/api/conversations/' . $this->conv->id . '/messages' . '?count=' . $count
        )->assertOk()
            ->json('messages');
        $this->assertEquals($count, count($responseMessages));

        for ($i = 0; $i < count($responseMessages); ++$i) {
            $this->assertEquals(
                $this->messages[$i]->id,
                $responseMessages[$i]['id']
            );
        }
    }
    public function testIndexCountAndOrder() {
        $count = random_int(0, count($this->messages));
        $count = 3;
        $responseMessages = $this->actingAs($this->user)->getJson(
            '/api/conversations/' . $this->conv->id . '/messages' . '?count=' . $count . '&order=desc'
        )->assertOk()
            ->json('messages');
        $this->assertEquals($count, count($responseMessages));

        for ($i = 0; $i < count($responseMessages); ++$i) {
            $this->assertEquals(
                $this->messages[count($this->messages) - $i - 1]->id,
                $responseMessages[$i]['id']
            );
        }
    }
    public function testIndexFromAndToId() {
        $fromIndex = random_int(0, count($this->messages) - 1);
        $toIndex = random_int($fromIndex, count($this->messages) - 1);
        $fromID = $this->messages[$fromIndex]->id;
        $toID = $this->messages[$toIndex]->id;
        $count = $toIndex - $fromIndex + 1;

        $responseMessages = $this->actingAs($this->user)->getJson(
            '/api/conversations/' . $this->conv->id . '/messages' . '?fromID=' . $fromID . '&toID=' . $toID
        )->assertOk()
            ->json('messages');
        $this->assertEquals($count, count($responseMessages));

        for ($i = 0; $i < count($responseMessages); ++$i) {
            $this->assertEquals(
                $this->messages[$i + $fromIndex]->id,
                $responseMessages[$i]['id']
            );
        }
    }
    public function testIndexFromIdCount() {
        $fromIndex = random_int(0, count($this->messages) - 1);
        $fromID = $this->messages[$fromIndex]->id;
        $count = random_int(0, count($this->messages) - 1 - $fromIndex);

        $responseMessages = $this->actingAs($this->user)->getJson(
            '/api/conversations/' . $this->conv->id . '/messages' . '?fromID=' . $fromID . '&count=' . $count
        )->assertOk()
            ->json('messages');
        $this->assertEquals($count, count($responseMessages));

        for ($i = 0; $i < count($responseMessages); ++$i) {
            $this->assertEquals(
                $this->messages[$i + $fromIndex]->id,
                $responseMessages[$i]['id']
            );
        }
    }

    public function testShow() {
        foreach ($this->messages as $m){
            $this->actingAs($this->user)->getJson("/api/conversations/" . $this->conv->id . '/messages/' . $m->id)
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'id' => $m->id
                    ]
                ]);
        }
    }

    public function testStoreText() {
        $this->actingAs($this->user)->postJson('/api/conversations/' . $this->conv->id . '/messages', [
            'text' => 'Test message'
        ])->assertOk()
            ->assertJson([
                'message' => 'Message sent'
            ])
        ;
        $this->actingAs($this->user)->getJson(
            '/api/conversations/' . $this->conv->id . '/messages' . '?count=1&order=desc'
        )->assertOk()
            ->assertJson([
                'messages' => [
                    0 => [
                        'text' => 'Test message',
                        'user_id' => $this->user->id,
                        'user' => $this->user->name,
                        'user_avatar_url' => $this->user->avatar_url,
                        'conversation_id' => $this->conv->id,
                        'conversation' => $this->conv->name,
                        'attachment_mime' => null
                    ]
                ]
            ])
        ;
    }
}
