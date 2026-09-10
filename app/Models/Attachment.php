<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attachable_type',
        'attachable_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    protected static function booted(): void
    {
        static::deleting(function (Attachment $attachment) {
            if ($attachment->file_path) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        });
    }
}
