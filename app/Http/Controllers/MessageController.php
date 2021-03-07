<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{

    public function index(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $fromID = request('fromID');
        $toID = request('toID');
        $fromDate = request('fromDate');
        $toDate = request('toDate');
        $count = request('count');
        $order = request('order');

        $reverse = false;
        $messages = $conversation->messages();
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
    }

    public function store(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $this->authorize(Message::class);

        $data = [];
        $message = null;
        if (request()->has('text')) {
            $data = request()->validate([
                'text' => 'required'
            ]);
            $data['user_id'] = request()->user()->id;
            $data['conversation_id'] = $conversation->id;
            $message = Message::create($data);
        } else {
            $mime = request()->file('media')->getClientMimeType();
            request()->validate([
                'media' => 'required|mimetypes:' . $mime
            ]);
            
            $path = request()->file('media')->store('public/media/' . $mime);
            $thumbnail = null;

            $message = Message::create([
                'attachment_mime' => $mime,
                'attachment_url' => $path,
                'attachment_thumbnail' => $thumbnail,
                
                'user_id' => request()->user()->id,
                'conversation_id' => $conversation->id
            ]);

            
        }

        if ($message === null) {
            abort(422, "The given data was invalid.");
            return null;
        }

        //broadcast(new MessageSent($message));
        broadcast(new MessageSent($message))->toOthers();

        return [
            'message' => 'Message sent'
        ];
    }

    public function show(Conversation $conversation, Message $message)
    {
        $this->authorize('view', $conversation);
        $this->authorize($message);

        return new MessageResource($message);
    }

    public function update(Conversation $conversation, Message $message)
    {
        // Messages cannot be edited

        /*$this->authorize('view', $conversation);
        $this->authorize($message);*/

        abort(404, 'Not found');
    }

    public function destroy(Conversation $conversation, Message $message)
    {
        // Messages cannot be deleted
        
        /*$this->authorize('view', $conversation);
        $this->authorize($message);*/

        abort(404, 'Not found');
    }
}
