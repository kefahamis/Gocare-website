<h2>New contact message</h2>
<p><strong>Name:</strong> {{ $contact->name }}</p>
<p><strong>Email:</strong> {{ $contact->email }}</p>
@if ($contact->phone)
    <p><strong>Phone:</strong> {{ $contact->phone }}</p>
@endif
@if ($contact->subject)
    <p><strong>Subject:</strong> {{ $contact->subject }}</p>
@endif
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($contact->message)) !!}</p>

