<!DOCTYPE html>
<html>
<head>
    <title>Event Reminder</title>
</head>
<body>
    <h1>Reminder: {{ $event->title }}</h1>
    
    <p>This is a reminder for your upcoming event:</p>
    
    <div style="margin: 20px 0; padding: 20px; background: #f8f9fa;">
        <p><strong>Event:</strong> {{ $event->title }}</p>
        <p><strong>Date:</strong> {{ $event->start_time->format('F j, Y') }}</p>
        <p><strong>Time:</strong> {{ $event->start_time->format('g:i A') }} - {{ $event->end_time->format('g:i A') }}</p>
        
        @if($event->description)
            <p><strong>Description:</strong> {{ $event->description }}</p>
        @endif
    </div>
    
    <p>Please make sure to attend on time.</p>
</body>
</html>
