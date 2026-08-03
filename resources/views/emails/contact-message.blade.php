<h2>New contact message</h2>
<p><strong>Name:</strong> {{ $message->name }}</p>
<p><strong>Email:</strong> {{ $message->email }}</p>
@if ($message->phone)
    <p><strong>Phone:</strong> {{ $message->phone }}</p>
@endif
@if ($message->subject)
    <p><strong>Subject:</strong> {{ $message->subject }}</p>
@endif
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($message->message)) !!}</p>

