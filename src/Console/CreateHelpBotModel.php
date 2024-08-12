<?php

namespace LaravelSklearnBot\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateHelpBotModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:helpbotmodel {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new HelpBot model class in the /Sklearnbot/HelpBot directory';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $directory = base_path('app/Sklearnbot/HelpBot');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $subname = 'HelpBot';

        $filePath = $directory . '/' . $name . $subname . '.php';

        if (File::exists($filePath)) {
            $this->error("File already exists at $filePath");
            return 1;
        }

        $fileContent = $this->getFileContent($name,$subname);

        File::put($filePath, $fileContent);

        $this->info("File created successfully at $filePath");

        return 0;
    }

     /**
     * Get the content for the new HelpBot model file from a stub.
     *
     * @param string $name
     * @return string
     */
    protected function getFileContent($name,$subname)
    {
        $stub = File::get(base_path('vendor/dog729/laravel-sklearnbot/stub/helpbot-model.stub'));

        return str_replace(['{{ className }}','{{ name }}'], [($name.$subname),$name], $stub);
    }

}