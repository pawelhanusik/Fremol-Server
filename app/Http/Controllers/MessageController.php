<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($conversationID)
    {
        $fromID = request('fromID');
        $toID = request('toID');
        $fromDate = request('fromDate');
        $toDate = request('toDate');
        $count = request('count');
        $order = request('order');

        $userConversation = request()->user()->conversations()->find($conversationID);
        if ($userConversation !== null) {
            
            $reverse = false;

            $messages = $userConversation->messages();
            
            if ($order === null) {
                if (
                    $count !== null
                    && ($toID !== null || $toDate !== null)
                ) {
                    $messages = $messages->orderBy('id', 'desc');
                    $reverse = true;
                }
            } else if($order == 'desc') {
                $messages = $messages->orderBy('id', 'desc');
            }

            if ($fromID !== null) {
                $messages = $messages->where('id', '>=', $fromID);
            }
            if ($toID !== null) {
                $messages = $messages->where('id', '<=', $toID);
            }
            if ($fromDate !== null) {
                $messages = $messages->where('created_at', '>=', new Carbon($fromDate));
            }
            if ($toDate !== null) {
                $messages = $messages->where('created_at', '<=', new Carbon($toDate));
            }
            if ($count !== null) {
                $messages = $messages->limit($count);
            }
            $messages = $messages->get();
            if ($reverse) {
                $messages = $messages->reverse();
            }
            return [
                'count' => $messages->count(),
                'messages' => MessageResource::collection($messages)
            ];
        } else {
            abort(401, "Invalid credentials");
            return null;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($conversationID)
    {
        $userConversation = request()->user()->conversations()->find($conversationID);
        if ($userConversation !== null) {
            $data = request()->validate([
                'text' => 'required'
            ]);
            $data['user_id'] = request()->user()->id;
            $data['conversation_id'] = $conversationID;
            $message = Message::create($data);
            
            //broadcast(new MessageSent($message));
            broadcast(new MessageSent($message))->toOthers();

            return [
                'message' => 'Message sent'
            ];
        } else {
            abort(401, "Invalid credentials");
            return null;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function show($conversationID, $messageID)
    {
        $userConversation = request()->user()->conversations()->find($conversationID);
        if ($userConversation !== null) {
            $message = $userConversation->messages->find($messageID);
            if ($message !== null) {
                return new MessageResource($message);
            } else {
                abort(404, "Not found");
                return null;
            }
        } else {
            abort(401, "Invalid credentials");
            return null;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        //
    }
}
