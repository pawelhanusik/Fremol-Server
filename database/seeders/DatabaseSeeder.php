<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        
        $adminUser = User::factory([
            'email' => 'example@example.com',
            'name' => 'admin'
        ])->create();

        $users = User::factory(10)->create();
        $conversations = Conversation::factory(3)->create();

        
        foreach($conversations as $conversation) {
            $adminUser->addToConversation($conversation);
            foreach($users as $user) {
                $user->addToConversation($conversation);
            }
        }
    }
}
