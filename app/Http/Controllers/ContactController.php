<?php

namespace App\Http\Controllers;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\ContactSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    /**
     * Why this submission looks automated, or null when it does not.
     *
     * Three cheap defences, no third-party service, nothing for a visitor to
     * solve:
     *
     *   website  a honeypot field, hidden from people and irresistible to bots
     *   started  a signed timestamp: a person cannot read and fill this form
     *            in under a couple of seconds, a script does it instantly
     *   limit    submissions per IP per hour, for the bot that beats both
     */
    private function spamReason(Request $request, ?ContactSetting $settings): ?string
    {
        if (filled($request->input('website'))) {
            return 'honeypot';
        }

        $minSeconds = $settings?->spam_min_seconds ?? 3;

        if ($minSeconds > 0) {
            // Signed, so it cannot be back-dated; missing means the payload
            // was not built from our form.
            try {
                $startedAt = (int) Crypt::decryptString((string) $request->input('started_at'));
            } catch (\Throwable) {
                return 'missing timestamp';
            }

            $elapsed = time() - $startedAt;

            if ($elapsed < $minSeconds) {
                return 'submitted too fast';
            }

            // A two-hour-old form is a replayed payload, not somebody thinking.
            if ($elapsed > 7200) {
                return 'stale form';
            }
        }

        $maxPerHour = $settings?->spam_max_per_hour ?? 5;

        if ($maxPerHour > 0) {
            $key = 'contact-form:' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, $maxPerHour)) {
                return 'rate limit';
            }

            RateLimiter::hit($key, 3600);
        }

        return null;
    }

    public function store(Request $request): RedirectResponse
    {
        $settings = ContactSetting::resolved();

        if ($settings && ! $settings->enabled) {
            return back()->with('error', 'The contact form is currently closed. Please try again later.');
        }

        if ($reason = $this->spamReason($request, $settings)) {
            Log::info('Contact submission rejected as spam', [
                'reason' => $reason,
                'ip' => $request->ip(),
            ]);

            // Deliberately the SAME response a real send gets. Telling a bot
            // which defence caught it is telling it how to get past next time,
            // and a false positive at least does not look broken to a human.
            return back()->with('success', $settings ? $settings->successMessage() : ContactSetting::DEFAULT_SUCCESS);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Stored first: the message is the thing that matters, and it has to
        // survive a mail server that is down or misconfigured.
        $contact = ContactMessage::create($data);

        $recipients = $settings ? $settings->recipientList() : array_filter([config('gocare.notification_email')]);
        $subject = $settings ? $settings->subjectLine() : ContactSetting::DEFAULT_SUBJECT;

        if ($recipients !== []) {
            try {
                $mail = Mail::to($recipients);

                if ($settings && $settings->ccList() !== []) {
                    $mail->cc($settings->ccList());
                }

                // Queued: a slow SMTP server must not hold the visitor's
                // request open while they wait for a thank-you page.
                $mail->queue(new ContactMessageReceived($contact, $subject));
            } catch (\Throwable $e) {
                // The message is already saved and visible in the admin, so a
                // mail failure is worth recording but not worth showing to
                // someone who did nothing wrong.
                Log::error('Contact notification could not be sent: ' . $e->getMessage(), [
                    'contact_message_id' => $contact->id,
                ]);
            }
        }

        if ($settings && $settings->auto_reply) {
            try {
                Mail::to($contact->email)->queue(new ContactAutoReply(
                    $settings->autoReplySubject(),
                    $settings->autoReplyBody($contact->name),
                ));
            } catch (\Throwable $e) {
                Log::warning('Contact auto-reply could not be sent: ' . $e->getMessage(), [
                    'contact_message_id' => $contact->id,
                ]);
            }
        }

        return back()->with('success', $settings ? $settings->successMessage() : ContactSetting::DEFAULT_SUCCESS);
    }
}
