<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UserTest extends TestCase
{
    private $user;

    protected function setUp() :void {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
    }

    public function testIndex() {
        $this->actingAs($this->user)->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure([
                'data'
            ])
        ;
    }
    public function testShow() {
        $this->actingAs($this->user)->getJson('/api/users/' . $this->user->id)
            ->assertOk()
            ->assertJson([
                'data' =>[
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ]
            ])
        ;
    }
    public function testUpdate() {
        $this->actingAs($this->user)->postJson('/api/users/' . $this->user->id . '/update', [
            'email' => $this->user->email,
            'name' => 'aaa',
            'oldPassword' => 'password'
        ])->assertOk();
        $this->actingAs($this->user)->postJson('/api/users/' . $this->user->id . '/update', [
            'email' => 'new_' . $this->user->email,
            'name' => 'bbb',
            'oldPassword' => 'password'
        ])->assertOk();
        $randomUserId = User::all()->random()->id;
        $response = $this->actingAs($this->user)->postJson('/api/users/' . $randomUserId . '/update', [
            'email' => $this->user->email,
            'name' => 'aaa',
            'oldPassword' => 'password'
        ]);
        if ($randomUserId == $this->user->id) {
            $response->assertStatus(200);
        } else {
            $response->assertStatus(403);
        }
    }
    public function testDelete() {
        $this->actingAs($this->user)->deleteJson('/api/users/' . $this->user->id)
            ->assertOk()
        ;
        $this->actingAs($this->user)->deleteJson('/api/users/' . $this->user->id)
            ->assertStatus(404)
        ;
        $randomUserId = User::all()->random()->id;
        $response = $this->actingAs($this->user)->deleteJson('/api/users/' . $randomUserId);
        if ($randomUserId == $this->user->id) {
            $response->assertStatus(404);
        } else {
            $response->assertStatus(403);
        }
    }
}
