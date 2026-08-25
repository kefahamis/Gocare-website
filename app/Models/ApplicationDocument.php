<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ApplicationDocument extends Model
{
    /** The three documents the form requires, plus the optional many. */
    public const KIND_ID = 'id_copy';
    public const KIND_RESULT_SLIP = 'result_slip';
    public const KIND_PHOTO = 'passport_photo';
    public const KIND_CERTIFICATE = 'certificate';

    /**
     * What each kind accepts, mirroring the limits printed on the form itself.
     * `single` kinds replace on re-upload; the rest accumulate.
     *
     * @var array<string, array{label: string, mimes: array<int, string>, max: int, single: bool, required: bool}>
     */
    public const KINDS = [
        self::KIND_ID => [
            'label' => 'National ID / Passport copy',
            'mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max' => 5120,
            'single' => true,
            'required' => true,
        ],
        self::KIND_RESULT_SLIP => [
            'label' => 'KCSE result slip / certificate',
            'mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max' => 5120,
            'single' => true,
            'required' => true,
        ],
        self::KIND_PHOTO => [
            'label' => 'Passport-size photo',
            'mimes' => ['jpg', 'jpeg', 'png'],
            'max' => 2048,
            'single' => true,
            'required' => true,
        ],
        self::KIND_CERTIFICATE => [
            'label' => 'Additional certificate',
            'mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max' => 5120,
            'single' => false,
            'required' => false,
        ],
    ];

    protected $fillable = [
        'application_id', 'kind', 'original_name', 'path', 'mime', 'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function label(): string
    {
        return self::KINDS[$this->kind]['label'] ?? $this->kind;
    }

    /** Human-readable size, for the admin list. */
    public function readableSize(): string
    {
        $bytes = (int) $this->size;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }

    /**
     * Delete the file along with the row. Without this an admin deleting a
     * document leaves the national ID sitting on disk for ever.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $document) {
            Storage::disk(config('gocare.documents_disk'))->delete($document->path);
        });
    }
}
