<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countEach = 10;
        $messages = [
            'AAAA',
            'BBBB',
            'CCCC'
        ];
        

        $adminUser = User::factory([
            'email' => 'example@example.com',
            'name' => 'admin'
        ])->create();
        $adminUser2 = User::factory([
            'email' => 'example2@example.com',
            'name' => 'admin2'
        ])->create();

        $conversation = Conversation::factory([
            'name' => 'test'
        ])->create();

        $users = [$adminUser, $adminUser2];
        foreach($users as $user) {
            $user->addToConversation($conversation);
        }
        
        //Add messages
        for($i = 0; $i < count($messages); ++$i) {
            Message::factory([
                'text' => $messages[$i]
            ])->count($countEach)
                ->for( User::all()->random() )
                ->for( Conversation::all()->random() )
                ->create();
        }
    }
}
