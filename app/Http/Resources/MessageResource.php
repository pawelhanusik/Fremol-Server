<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'user_id' => $this->user_id,
            'user' => $this->user->name,
            'conversation_id' => $this->conversation_id,
            'conversation' => $this->conversation->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}