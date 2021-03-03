<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConversationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conversation  $conversation
     * @return mixed
     */
    public function view(User $user, Conversation $conversation)
    {
        return $user->conversations->contains($conversation);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conversation  $conversation
     * @return mixed
     */
    public function update(User $user, Conversation $conversation)
    {
        return $conversation->creator->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conversation  $conversation
     * @return mixed
     */
    public function delete(User $user, Conversation $conversation)
    {
        return $conversation->creator->is($user);
    }

    /**
     * Determine whether the user can leave the conversation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conversation  $conversation
     * @return mixed
     */
    public function leave(User $user, Conversation $conversation)
    {
        return $conversation->creator->isNot($user);
    }
}
