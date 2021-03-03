<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ConversationResource::collection( auth()->user()->conversations );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->authorize(Conversation::class);

        $data = request()->validate([
            'name' => 'required',
            'participants' => 'array'
        ]);
        
        $conversation = Conversation::create([
            'name' => $data['name'],
            'creator_id' => auth()->user()->id
        ]);
        $participants = $data['participants'];
        $participants[] = auth()->user()->id;
        $conversation->users()->sync($participants);
        
        return [
            'message' => 'Conversation created',
            'conversation' => new ConversationResource($conversation)
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Conversation  $conversation
     * @return \Illuminate\Http\Response
     */
    public function show(Conversation $conversation)
    {
        $this->authorize($conversation);

        return new ConversationResource($conversation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Conversation  $conversation
     * @return \Illuminate\Http\Response
     */
    public function update(Conversation $conversation)
    {
        $this->authorize($conversation);

        $data = request()->validate([
            'name' => 'required',
            'participants' => 'array'
        ]);
        $conversation->update([
            'name'=> $data['name']
        ]);
        
        $participants = $data['participants'];
        $participants[] = auth()->user()->id;
        $conversation->users()->sync($participants);

        return null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Conversation  $conversation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Conversation $conversation)
    {
        $this->authorize($conversation);

        $conversation->delete();
        return null;
    }

    /**
     * Leave the conversation
     *
     * @param  \App\Models\Conversation  $conversation
     * @return \Illuminate\Http\Response
     */
    public function leave(Conversation $conversation) {
        if (request()->user()->cannot('leave', $conversation)) {
            abort(403, 'You cannot leave conversation you have created.');
        }

        request()->user()->removeFromConversation($conversation);
        return null;
    }
}
