<?php

namespace LaravelSklearnBot\Models;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    /**
     *The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sklearnbot_search';

    /**
     *Attributes that can be massively assigned.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'title',
        'description',
        'data',
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