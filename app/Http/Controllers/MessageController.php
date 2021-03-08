<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Exporters\EncodingException;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

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
            $isMedia = request()->has('isMedia') && request('isMedia') == "true" ? true : false;
            $storePath = 'public/';
            if ($isMedia) {
                // media
                $storePath .= 'media/';
                $mime = request()->file('attachment')->getClientMimeType();
                $storePath .= explode('/', $mime)[0];
                request()->validate([
                    'attachment' => 'required|mimetypes:' . $mime
                ]);
            } else {
                // attachment
                $storePath .= 'attachments/';
                $mime = 'other';
            }
            $path = request()->file('attachment')->store($storePath);
            $thumbnail = null;

            if ($isMedia) {
                if (strpos($mime, "video") === 0) {
                    // VIDEO

                    // generate thumbnail
                    $ffprobe = FFProbe::create();
                    $videoLen = $ffprobe->format(request()->file('attachment'))->get('duration');
                    $videoLen = explode(".", $videoLen)[0];
                    
                    $thumbnailPath = $path . '.thumbnail.png';
                    try {
                        FFMpeg::open($path)
                            ->getFrameFromSeconds(
                                rand(0, min($videoLen / 10, 60))
                            )
                            ->export()
                            ->save($thumbnailPath);
                    } catch (EncodingException) {
                        Log::error('MessageController::store(): Error generating thumbnail of a video.');
                        abort(500, "There was an error while processing your video. Please try againg leater");
                        return null;
                    }
                    $thumbnail = $thumbnailPath;

                    // convert video to mp4
                    $pathConverted = substr($path, 0, strrpos($path, '.')) . '.mp4';
                    try {
                        FFMpeg::open($path)
                            ->export()
                            ->inFormat(new \FFMpeg\Format\Video\X264('aac'))
                            ->save($pathConverted);
                    } catch (EncodingException) {
                        Log::error('MessageController::store(): Error converting video.');
                        abort(500, "There was an error while processing your video. Please try againg leater");
                        return null;
                    }
                    Storage::delete($path);
                    $path = $pathConverted;
                }
            }

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
