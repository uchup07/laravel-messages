<?php

namespace Uchup07\Messages\Traits;

use Carbon\Carbon;
use Uchup07\Messages\Events\NewMessageDispatched;
use Uchup07\Messages\Events\NewReplyDispatched;
use Uchup07\Messages\Models\Thread;

trait HasMessage
{
    protected $subject, $message;
    protected $recipients = [];
    protected $attachments = [];
    protected $threadsTable, $messagesTable, $participantsTable, $attachmentsTable;
    protected $threadClass, $participantClass, $attachmentClass;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->threadsTable = config('laravel-messages.tables.threads');
        $this->messagesTable = config('laravel-messages.tables.messages');
        $this->participantsTable = config('laravel-messages.tables.participants');
        $this->attachmentsTable = config('laravel-messages.tables.attachments');

        $this->threadClass = config('laravel-messages.models.thread');
        $this->participantClass = config('laravel-messages.models.participant');
        $this->attachmentClass = config('laravel-messages.models.attachment');

        parent::__construct($attributes);
    }

    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function writes($message)
    {
        $this->message = $message;

        return $this;
    }

    public function attachments($attachments)
    {
        if (is_array($attachments)) {
            $this->attachments = array_merge($this->attachments, $attachments);
        } else {
            $this->attachments[] = $attachments;
        }

        return $this;
    }

    public function to($users)
    {
        if (is_array($users)) {
            $this->recipients = array_merge($this->recipients, $users);
        } else {
            $this->recipients[] = $users;
        }

        return $this;
    }


    /**
     * Send new message
     *
     * @return mixed
     */
    public function send()
    {
        $thread = $this->threads()->create([
            'subject' => $this->subject,
        ]);

        // Message
        $message = $thread->messages()->create([
            'user_id' => $this->id,
            'body' => $this->message
        ]);

        // Sender
        $participantClass = $this->participantClass;
        $participantClass::create([
            'user_id' => $this->id,
            'thread_id' => $thread->id,
            'seen_at' => Carbon::now()
        ]);

        if (count($this->recipients)) {
            $thread->addParticipants($this->recipients);
        }

        // Attachment
        $attachmentClass = $this->attachmentClass;
        $attachmentClass::create([
            'user_id' => $this->id,
            'message_id' => $message->id,
            'title' => '',
            'file' => ''
        ]);

        if ($thread) {
            event(new NewMessageDispatched($thread, $message));
        }

        return $thread;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Thread $thread
     *
     * @return \Illuminate\Http\Response
     */
    public function reply($thread)
    {
        if ( ! is_object($thread)) {
            $threadClass = $this->threadClass;
            $thread = $threadClass::whereId($thread)->firstOrFail();
        }

        $thread->activateAllParticipants();

        $message = $thread->messages()->create([
            'user_id' => $this->id,
            'body' => $this->message
        ]);

        // Add replier as a participant
        $participantClass = $this->participantClass;
        $participant = $participantClass::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $this->id
        ]);

        $participant->seen_at = Carbon::now();
        $participant->save();

        $thread->updated_at = Carbon::now();
        $thread->save();

        event(new NewReplyDispatched($thread, $message));

        return $message;
    }

    /**
     * Get user threads
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads()
    {
        return $this->hasMany($this->threadClass);
    }

    /**
     *
     * @param bool $withTrashed
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function participated($withTrashed = false)
    {
        $query = $this->belongsToMany($this->threadClass, $this->participantsTable, 'user_id', 'thread_id')
            ->withPivot('seen_at')
            ->withTimestamps();

        if ( ! $withTrashed) {
            $query->whereNull("{$this->participantsTable}.deleted_at");
        }

        return $query;
    }

    /**
     * Get the threads that have been sent to the user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function received()
    {
        // todo: get only the received messages if they got an answer
        return $this->participated()->latest('updated_at');
    }

    /**
     * Get the threads that have been sent by a user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function sent()
    {
        return $this->participated()
            ->where("{$this->threadsTable}.user_id", $this->id)
            ->latest('updated_at');
    }

    /**
     * Get unread messages
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function unread()
    {
        return $this->received()->whereNull('seen_at');
    }
}