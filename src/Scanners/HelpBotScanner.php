<?php

namespace LaravelSklearnBot\Scanners;

use Illuminate\Support\Facades\File;
use LaravelSklearnBot\Contracts\HelpBotContract;

class HelpBotScanner
{
    /**
     * Scan the directory for PHP files and execute `push` method on classes implementing HelpBotContract.
     *
     * @return array
     */
    public function scan()
    {
        $baseDir = app_path('Sklearnbot/HelpBot');
        return $this->scanDirectory($baseDir) ?? [];
    }

    /**
     * Scan a directory for PHP files recursively.
     *
     * @param string $directory
     * @return void
     */
    protected function scanDirectory($directory)
    {
        $arArrayResult = [];

        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if($_result = $this->processFile($file)){
                $arArrayResult[] = $_result;
            }
        }

        return $arArrayResult;
    }

    /**
     * Process an individual PHP file.
     *
     * @param \SplFileInfo $file
     * @return void
     */
    protected function processFile($file)
    {
        $className = $this->getClassNameFromFile($file->getPathname());

        if (class_exists($className)) {
            $instance = new $className();
            
            //
            if ($instance instanceof HelpBotContract) {
                if (method_exists($instance, 'push') && method_exists($instance, 'type')) {
                    if($result = $instance->push()){
                        return ['result' => $result, 'type' => $instance->type(), 'class' => $instance];
                    }else{
                        return ;
                    }
                }
            }
        }
    }

    /**
     * Get the fully qualified class name from a file path.
     *
     * @param string $filePath
     * @return string|null
     */
    protected function getClassNameFromFile($filePath)
    {
        $content = file_get_contents($filePath);
        $namespace = '';
        $class = '';

        if (preg_match('/namespace\s+(.+?);/', $content, $matches)) {
            $namespace = $matches[1];
        }

        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            $class = $matches[1];
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }
}