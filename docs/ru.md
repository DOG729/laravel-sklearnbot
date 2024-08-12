# LaravelSklearnBot

LaravelSklearnBot - это пакет для Laravel, который позволяет парсить данные, создавать и обучать модели, а также использовать их для генерации ответов на запросы.

## Установка

1. Установите необходимые зависимости:

```
pip install flask scikit-learn python-dotenv
```

2. Добавьте `LaravelSklearnBot` в ваш `composer.json` и выполните команду:

```
composer require dog729/laravel-sklearnbot
```

## Использование

### Примеры вызова методов

Для использования пакета, вызовите следующие методы фасада `SklearnbotFacade`:

1. **Парсинг данных с обучением**:
    ```php
    \LaravelSklearnBot\SklearnbotFacade::parserHelpBotModel();
    ```

2. **Переобучение модели**:
    ```php
    \LaravelSklearnBot\SklearnbotFacade::trainModelFromScratch();
    ```

3. **Получение ответа по запросу**:
    ```php
    $result = \LaravelSklearnBot\SklearnbotFacade::getHelpBotResponse('hi');
    print_r($result);
    ```

4. **Дообучение модели**:
    ```php
    \LaravelSklearnBot\SklearnbotFacade::fineTuneModelHelpBot([
        [
            'id' => 8, 'type' => 'sw', 'title' => 'Darth Vader', 'text' => 'Anakin pam pam',
        ]
    ]);

    $result = \LaravelSklearnBot\SklearnbotFacade::getHelpBotResponse('Darth Vader');
    print_r($result); // Вернет: Array ( [action] => Array ( ) [id] => 8 [text] => Anakin pam pam [type] => sw )
    ```

### Консольные команды

1. **Создание экземпляра файла обработчика helpbot**:

    ```
    php artisan make:helpbotmodel {name}
    ```

2. **Установка пакета и генерация необходимых файлов**:

    ```
    php artisan helpbot:install
    ```

    Эта команда произведет установку, сгенерирует токен и создаст файл `helpbot.py`.

### Конфигурация

Файл конфигурации `config/sklearnbot.php` содержит следующие параметры:

```php

return [
    /**
     * logic for the operation of submodules
     */
    'helpbot' => 'python', 
    'python' => [
        'run' => 'app.py',
         /**
         * host:port for the running flash application
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

### Python-скрипт

Python-скрипт, который работает с этим пакетом, должен находиться в папке `py` и называться `app.py`. Его можно установить на другой сервер.

### Запуск и тестирование

Примеры для тестирования через `curl`:

1. **Создание модели**:
    ```
    curl -X POST http://127.0.0.1:5729/create-model -H "Content-Type: application/json" -H "Authorization: your-secure-token"
    ```

2. **Перезагрузка модели**:
    ```
    curl -X POST http://127.0.0.1:5729/reload-model -H "Content-Type: application/json" -H "Authorization: your-secure-token"
    ```

3. **Доопучение модели**:
    ```
    curl -X POST http://127.0.0.1:5729/fine-tune-model -H "Content-Type: application/json" -H "Authorization: your-secure-token" -d '[{"id":"3","title":"boba","text":"aboba"}]'
    ```

4. **Получение ответа**:
    ```
    curl -X POST http://127.0.0.1:5729/get-response -H "Content-Type: application/json" -d '{"text":"boba"}'
    ```
