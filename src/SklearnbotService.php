<?php

namespace LaravelSklearnBot;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class SklearnbotService
{

   use \LaravelSklearnBot\Traits\HandlesHelpBot;
   use \LaravelSklearnBot\Traits\HandlesSearch;

}