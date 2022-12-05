<?php

namespace Uchup07\Messages\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Multicaret\Inbox\Models\Message;
use Multicaret\Inbox\Models\Thread;

class MessageDispatched extends Notification implements ShouldQueue
{
    use Queueable;

    public $thread, $message, $participant;

    /**
     * Create a new notification instance.
     *
     * @param Thread  $thread
     * @param Message $message
     * @param         $participant
     */
    public function __construct($thread, $message, $participant)
    {
        $this->thread = $thread;
        $this->message = $message;
        $this->participant = $participant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return config('laravel-messages.notifications.via', [
            'mail',
            'database',
            'broadcast'
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'thread_id' => $this->thread->id,
            'message_id' => $this->message->id,
            'isReply' => $this->thread->messages()->count() >= 2,
        ];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->message->user->name . ' ' . trans('laravel-messages::la-messages.notification.subject'),
            'name' => $this->thread->user->name,
            'email' => $this->thread->user->email,
            'url' => route(config('laravel-messages.route.name') . 'message.show', $this->thread),
            'message' => $this->thread->subject,
            'icon' => 'communication/com002.svg',
            'thread_id' => $this->thread->id,
            'message_id' => $this->message->id,
            'isReply' => $this->thread->messages()->count() >= 2,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->message->user->name . ' ' . trans('laravel-messages::la-messages.notification.subject'),
            'name' => $this->thread->user->name,
            'email' => $this->thread->user->email,
            'url' => route(config('laravel-messages.route.name') . 'message.show', $this->thread),
            'message' => $this->thread->subject,
            'icon' => 'communication/com002.svg',
            'thread_id' => $this->thread->id,
            'message_id' => $this->message->id,
            'isReply' => $this->thread->messages()->count() >= 2,
        ]);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws \Throwable
     */
    public function toMail($notifiable)
    {
        $buttonUrl = route(config('laravel-messages.route.name') . 'message.show', $this->thread);
        $isReply = $this->thread->messages()->count() >= 2;
        $greeting = $isReply ? 'Re: ' . $this->thread->subject : $this->thread->subject;

        return (new MailMessage)
            ->success()
            ->subject($this->message->user->name . ' ' . trans('laravel-messages::la-messages.notification.subject') . ' - ' . config('app.name'))
            ->greeting($greeting)
            ->line(new HtmlString($this->message->body))
            ->action(trans('laravel-messages::la-messages.notification.button'), $buttonUrl);
    }

    /**
     * Get the type of the notification being broadcast.
     *
     * @return string
     */
    public function broadcastType()
    {
        return 'broadcast.message';
    }
}