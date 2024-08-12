# LaravelSklearnBot

LaravelSklearnBot is a Laravel package that allows you to parse data, create and train models, and use them to generate responses to requests.

## Installation

1. Install the required dependencies:

```
pip install flask scikit-learn python-dotenv
```

2. Add `LaravelSklearnBot` to your `composer.json` and run the following command:

```
composer require dog729/laravel-sklearnbot
```

## Usage

### Example Method Calls

To use the package, invoke the following methods from the `SklearnbotFacade`:

1. **Parse data with training**:
    ```php
    \LaravelSklearnBot\SklearnbotFacade::parserHelpBotModel();
    ```

2. **Retrain the model from scratch**:
    ```php
    \LaravelSklearnBot\SklearnbotFacade::trainModelFromScratch();
    ```

3. **Get a response from the model based on a query**:
    ```php
    $result = \LaravelSklearnBot\SklearnbotFacade::getHelpBotResponse('hi');
    print_r($result);
    ```

4. **Fine-tune the model**:
    ```php
    \LaravelSklearnBot\SklearnbotFacade::fineTuneModelHelpBot([
        [
            'id' => 8, 'type' => 'sw', 'title' => 'Darth Vader', 'text' => 'Anakin pam pam',
        ]
    ]);

    $result = \LaravelSklearnBot\SklearnbotFacade::getHelpBotResponse('Darth Vader');
    print_r($result); // Returns: Array ( [action] => Array ( ) [id] => 8 [text] => Anakin pam pam [type] => sw )
    ```

### Console Commands

1. **Create a helpbot model handler file**:

    ```
    php artisan make:helpbotmodel {name}
    ```

2. **Install the package and generate necessary files**:

    ```
    php artisan helpbot:install
    ```

    This command will install the package, generate a token, and create the `helpbot.py` file.

### Configuration

The configuration file `config/sklearnbot.php` contains the following parameters:

```php

return [
    /**
     * Logic for the operation of submodules
     */
    'helpbot' => 'python', 
    'python' => [
        'run' => 'app.py',
         /**
         * host:port for the running Flask application
         */
        'helpbot_token' => ENV('HELPBOT_TOKEN'),
        'helpbot_host' => ENV('HELPBOT_FLASK_HOST',"127.0.0.1"),
        'helpbot_port' => ENV('HELPBOT_FLASK_PORT',"5729"),
        /**
         * The name of the model files
         */
        'helpbot_model' => 'helpbot',
        'helpbot_model_addtraining' => true, //auto further training of the model
        'helpbot_pkl' => ENV('HELPBOT_PKL','model.pkl'), //model helpbot
        'helpbot_output_pkl' => ENV('HELPBOT_OUTPUT_PKL','output.pkl'), //model helpbot database
    ],
];
```

### Running and Testing

Examples for testing with `curl`:

1. **Create a model**:
    ```
    curl -X POST http://127.0.0.1:5729/create-model -H "Content-Type: application/json" -H "Authorization: your-secure-token"
    ```

2. **Reload the model**:
    ```
    curl -X POST http://127.0.0.1:5729/reload-model -H "Content-Type: application/json" -H "Authorization: your-secure-token"
    ```

3. **Fine-tune the model**:
    ```
    curl -X POST http://127.0.0.1:5729/fine-tune-model -H "Content-Type: application/json" -H "Authorization: your-secure-token" -d '[{"id":"3","title":"boba","text":"aboba"}]'
    ```

4. **Get a response**:
    ```
    curl -X POST http://127.0.0.1:5729/get-response -H "Content-Type: application/json" -d '{"text":"boba"}'
    ```