<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />


</head>

<body>
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
</body>
