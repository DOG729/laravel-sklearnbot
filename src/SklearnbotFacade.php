<?php

namespace LaravelSklearnBot;

use Illuminate\Support\Facades\Facade;

class SklearnbotFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'sklearnbot';
    }


}