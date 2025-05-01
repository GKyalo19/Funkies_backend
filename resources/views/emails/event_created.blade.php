<h1>Hi {{ Auth::user()->name }},</h1>
<p>Your event <strong>{{ $event->name }}</strong> has been successfully created!</p>
<p>Details:</p>
<ul>
  <li>Venue: {{ $event->venue }}</li>
  <li>Start Date: {{ $event->startDate }}</li>
  <li>End Date: {{ $event->endDate }}</li>
  <li>Category: {{ $event->category }}</li>
</ul>
<p>Thank you for using our platform!</p>
