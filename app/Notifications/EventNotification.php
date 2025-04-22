<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNotification extends Notification
{
    use Queueable;

    protected $event;  // The event data

    /**
     * Create a new notification instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Event Notification: ' . $this->event->title)
            ->greeting('Hello' . $notifiable->name . '!')
            ->line('A new event that matches your interests has been added')
            ->line('Event: ' . $this->event->title)
            ->line('Date: ' . $this->event->date)
            ->action('View Event', url('/events/' . $this->event->id))
            ->line('Thank you for staying updated with us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id'=>$this->event->id,
            'title'=>$this->event->title,
            'message'=>'A new event "'.$this->event->title.'"has been added.',
        ];
    }
}
