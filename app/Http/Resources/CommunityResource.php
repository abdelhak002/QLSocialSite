<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommunityResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'avatarImage' => new ImageResource($this->whenLoaded('avatarImage')),
            'coverImage' => new ImageResource($this->whenLoaded('coverImage')),
            'currentIsMember' => $this->currentIsMember(),
            'members_count' => $this->members_count,
            'url' => $this->resource->url,
        ];
    }
}
