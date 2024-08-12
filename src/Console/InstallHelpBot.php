<?php

namespace LaravelSklearnBot\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallHelpBot extends Command
{
    // Define the command signature and description
    protected $signature = 'helpbot:install';
    protected $description = 'Install HelpBot by generating a token and creating the app.py file';

    public function handle()
    {
        // Generate a random token
        $token = Str::random(32);

        // Add the token to the .env file
        $this->updateEnvFile('HELPBOT_TOKEN', $token);

        // Create the app.py file
        $this->createPythonAppFile($token);

        $this->info('HelpBot has been successfully installed.');
    }

    // Method to update the .env file
    protected function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            // Update the existing key or add it if it doesn't exist
            if (Str::contains(File::get($path), "{$key}=")) {
                File::put($path, preg_replace(
                    "/{$key}=.*/",
                    "{$key}={$value}",
                    File::get($path)
                ));
            } else {
                File::append($path, "{$key}={$value}\n");
            }

            $this->info("The {$key} has been added to your .env file.");
        }
    }

    // Method to get the content of the file based on the template
    protected function getFileContent($token)
    {
        $stub = File::get(base_path('vendor/dog729/laravel-sklearnbot/stub/python-app.stub'));

        return str_replace(
            ['{{ path }}', '{{ data_json }}', '{{ model_pkl }}', '{{ output_data_pkl }}', '{{ TOKEN }}'],
            ['./storage/app/sklearnbot/', 'helpbot.json', config('sklearnbot.python.helpbot_pkl'), config('sklearnbot.python.helpbot_output_pkl'), $token],
            $stub
        );
    }

    // Method to create the helpbot.py file
    protected function createPythonAppFile($token)
    {
        $content = $this->getFileContent($token);
        $path = base_path('helpbot.py');

        if (!File::exists($path)) {
            File::put($path, $content);
            $this->info('The helpbot.py file has been created.');
        } else {
            $this->warn('The helpbot.py file already exists.');
        }
    }
}