<?php

namespace Uchup07\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['title', 'user_id', 'file'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('laravel-messages.tables.attachment');

        parent::__construct($attributes);
    }

    /**
     * Thread relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function message()
    {
        return $this->belongsTo(config('laravel-messages.models.message'));
    }

    /**
     * User relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Set the user id.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = (int)$value;
    }
}