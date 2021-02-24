<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
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

        $users[] = $adminUser;
        foreach($conversations as $conversation) {
            foreach($users as $user) {
                $user->addToConversation($conversation);
            }
        }
        
        //Add messages
        for($i = 0; $i < 1000; ++$i) {
            Message::factory()
                ->for( User::all()->random() )
                ->for( Conversation::all()->random() )
                ->create();
        }
    }
}
