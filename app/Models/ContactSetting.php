<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ContactSetting extends Model
{
    protected $fillable = [
        'enabled', 'recipients', 'cc', 'subject', 'success_message',
        'auto_reply', 'auto_reply_subject', 'auto_reply_body',
        'spam_min_seconds', 'spam_max_per_hour',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'auto_reply' => 'boolean',
        'recipients' => 'array',
        'cc' => 'array',
        'spam_min_seconds' => 'integer',
        'spam_max_per_hour' => 'integer',
    ];

    public const DEFAULT_SUBJECT = 'New GoCare contact message';
    public const DEFAULT_SUCCESS = 'Your message has been sent successfully.';
    public const DEFAULT_AUTO_REPLY_SUBJECT = 'We have received your message';
    public const DEFAULT_AUTO_REPLY_BODY = "Hello {name},\n\nThank you for contacting GoCare Training Institute. We have received your message and will reply shortly.\n\nBest regards,\nGoCare Training Institute";

    public static function current(): self
    {
        return static::query()->firstOrCreate([], ['enabled' => true]);
    }

    /**
     * The saved row, or null when the table is not there yet.
     *
     * Read on a public request, so it must survive a database that has not
     * been migrated: a contact form that 500s because a settings table is
     * missing is worse than one running on its old defaults.
     */
    public static function resolved(): ?self
    {
        try {
            if (! Schema::hasTable('contact_settings')) {
                return null;
            }

            return static::query()->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Where a contact message goes. Falls back to the configured notification
     * address, which is what the site used before this was editable.
     *
     * @return array<int, string>
     */
    public function recipientList(): array
    {
        $list = array_filter(array_map('trim', (array) ($this->recipients ?? [])));

        return $list !== [] ? array_values($list) : array_filter([config('gocare.notification_email')]);
    }

    /** @return array<int, string> */
    public function ccList(): array
    {
        return array_values(array_filter(array_map('trim', (array) ($this->cc ?? []))));
    }

    public function subjectLine(): string
    {
        return $this->subject ?: self::DEFAULT_SUBJECT;
    }

    public function successMessage(): string
    {
        return $this->success_message ?: self::DEFAULT_SUCCESS;
    }

    public function autoReplySubject(): string
    {
        return $this->auto_reply_subject ?: self::DEFAULT_AUTO_REPLY_SUBJECT;
    }

    /**
     * The acknowledgement body with {name} filled in.
     */
    public function autoReplyBody(string $name): string
    {
        $body = $this->auto_reply_body ?: self::DEFAULT_AUTO_REPLY_BODY;

        return str_replace('{name}', $name, $body);
    }
}
