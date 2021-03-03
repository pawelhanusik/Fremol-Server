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
        $adminUser2 = User::factory([
            'email' => 'example2@example.com',
            'name' => 'admin2'
        ])->create();

        $users = User::factory(10)->create();
        $conversations = Conversation::factory(3)->for($adminUser, 'creator')->create();

        $users[] = $adminUser;
        $users[] = $adminUser2;
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
