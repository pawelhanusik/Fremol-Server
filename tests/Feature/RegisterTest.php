<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    private function newUniqueName() {
        $name = '';
        do {
            $name = 'testuser_' . now()->timestamp . '_' . random_int(0, 999999);
        } while (User::where('name', $name)->count() > 0);
        return $name;
    }
    private function newUniqueEmail() {
        $email = '';
        do {
            $email = 'testuser_' . now()->timestamp . '_' . random_int(0, 999999) . '@example.com';
        } while (User::where('email', $email)->count() > 0);
        return $email;
    }

    public function testRegister() {
        $name = $this->newUniqueName();
        $email = $this->newUniqueEmail();
        $password = 'password';
        
        $this->postJson('/api/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password2' => $password
        ])->assertOk()
            ->assertJson([
                'message' => 'User has been registered'
            ])
        ;
        
        $this->postJson('/api/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password2' => $password
        ])->assertStatus(422);
    }
    public function testLogin() {
        $user = User::factory()->create();
        
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ])->assertOK()
            ->assertJsonStructure([
                'token',
                'userName'
            ])->assertJson([
                'userName' => $user->name
            ])
        ;
        
        $this->postJson('/api/login/check')
            ->assertOk()
            ->assertJson([
                'message' => 'OK'
            ])
        ;

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ])
        ;
    }
}
