<h2>New GoCare application</h2>
<p><strong>Reference:</strong> {{ $application->reference }}</p>
<p><strong>Submitted:</strong> {{ $application->submitted_at }}</p>
<pre>{{ json_encode($application->data, JSON_PRETTY_PRINT) }}</pre>

