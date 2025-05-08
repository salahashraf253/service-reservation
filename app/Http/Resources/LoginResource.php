<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this['access_token'],
            'is_admin' => $this['is_admin'],
        ];
    }
    
    public function with(Request $request): array
    {
        return [
            'message' => 'User logged in successfully',
        ];
    }
}
