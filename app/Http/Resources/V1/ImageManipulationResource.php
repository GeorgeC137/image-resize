<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageManipulationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' =>$this->id,
            'name' =>$this->name,
            'type' =>$this->type,
            'original' => URL::to($this->path),
            'output' => URL::to($this->output_path),
            'album_id' =>$this->album_id,
            'created_at' =>$this->created_at
        ];
    }
}
