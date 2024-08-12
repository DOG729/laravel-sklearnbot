<?php

namespace LaravelSklearnBot\Models;

use Illuminate\Database\Eloquent\Model;

class Helpbot extends Model
{
    /**
     *The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sklearnbot_helpbot';

    /**
     *Attributes that can be massively assigned.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'hash',
        'title',
        'synonym',
        'text',
        'action',
        'data',
        'belongs_to',
        'weight',
        'class',
        'class_id'
    ];

    /**
     * Attributes that should be converted to native types.
     *
     * @var array
     */
    protected $casts = [
        'action' => 'array',
        'data' => 'array',
    ];

    /**
     * Checking if the model belongs to sklearnbot
     */
    public static function getOwnerModel()
    {
        return 'sklearnbot';
    }

}