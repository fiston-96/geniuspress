<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'disk', 'path', 'original_name', 'mime_type', 'size', 'width', 'height', 'alt'])]
class Media extends Model
{
    protected $table = 'media';

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
