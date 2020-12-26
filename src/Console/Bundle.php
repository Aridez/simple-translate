<?php

namespace Aridez\SimpleTranslate\Console;

use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class Bundle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:bundle';

    /**
     * The bundle folder path
     *
     * @var string
     */
    protected $bundle_path;

    /**
     * The laravel lang folder path
     *
     * @var string
     */
    protected $lang_path;

    /**
     * The laravel js resources folder path
     *
     * @var string
     */
    protected $resources_path;

    /**
     * The path to the CustomBundles.json file containing additional tailored bundles
     *
     * @var string
     */
    protected $custom_file;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates translation bundles used by the simple-translate package';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->bundle_path = base_path() . "\\resources\\simple-translate\\bundles\\";
        $this->lang_path = base_path() . "\\resources\\lang\\";
        $this->resources_path = base_path() . "\\resources\\js\\";
        $this->custom_file = base_path() . "\\resources\\simple-translate\\custom-bundles.json";
    }

    /**
     * Execute the console command to generate json bundles inside the resources\simple-translate folder
     *
     * @return int
     */
    public function handle()
    {
        $locales = $this->getLocaleList();
        if (!$locales) {
            $this->error('No translations were found inside the resources\lang folder!');
            return;
        }

        $this->prepareBundleFolders($locales);

        foreach ($this->getFileIterator($this->resources_path, '.vue') as $file) {
            $translation_keys = $this->getTranslationKeys($file[0]);
            if (!$translation_keys) {
                continue;
            }

            $this->info('Bundling ' . $file[0]);
            foreach ($locales as $locale) {
                App::setLocale($locale);
                $bundle_file = $this->bundle_path . $locale . "\\" . pathinfo($file[0])['filename'] . ".json";
                $translation_array = $this->createTranslationBundle($translation_keys, $locale);
                if (file_exists($bundle_file)) {
                    $translation_array_merge = array_merge($translation_array, json_decode(file_get_contents($bundle_file), true));
                    file_put_contents($bundle_file, json_encode($translation_array_merge));
                } else {
                    file_put_contents($bundle_file, json_encode($translation_array));
                }
            }
        }

        foreach (json_decode(file_get_contents($this->custom_file), true) as $bundle_config) {
            $this->info('Creating custom bundle: ' . $bundle_config['name']);
            foreach ($locales as $locale) {
                $bundle_file =  $this->bundle_path . $locale . "\\" . $bundle_config['name'] . ".json";
                $custom_bundle = $this->mergeBundles($bundle_config['bundles'], $locale);
                $translation_array = $this->createTranslationBundle($bundle_config['keys'], $locale);
                $translation_array_merge = array_merge($custom_bundle, $translation_array);
                file_put_contents($bundle_file, json_encode($translation_array_merge));
            }

        }

    }

    /**
     * Gets a list of the locales present in the resources\lang folder
     *
     * @return array
     */
    protected function getLocaleList()
    {
        $files = array_diff(scandir($this->lang_path), array('..', '.'));
        $locales = [];
        foreach ($files as $file) {
            $locale = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);
            if (!in_array($locale, $locales)) {
                $locales[] = $locale;
            }
        }
        return $locales;
    }

    /**
     * Creates a resources\simple-translate folder if it doesn't exist
     * Removes all files and folders inside resources\simple-translate
     * Creates all lang folders again
     *
     * @param  string $locales
     * @return void
     */
    protected function prepareBundleFolders($locales)
    {
        if (!file_exists($this->bundle_path)) {
            mkdir($this->bundle_path, 0777, true);
        } else {
            $di = new RecursiveDirectoryIterator($this->bundle_path, FilesystemIterator::SKIP_DOTS);
            $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($ri as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }
        }

        foreach ($locales as $locale) {
            if (!file_exists($this->bundle_path . $locale)) {
                mkdir($this->bundle_path . $locale);
            }
        }

        if (!file_exists($this->custom_file)) {
            file_put_contents($this->custom_file, json_encode([]));
        }

    }

    /**
     * Gets a file iterator for the $directory folder to find all the files ending with $extension
     *
     * @param  string $directory
     * @param  string $extension
     * @return RegexIterator
     */
    protected function getFileIterator($directory, $extension)
    {
        $dir = new RecursiveDirectoryIterator($directory);
        $iterator = new RecursiveIteratorIterator($dir);
        return new RegexIterator($iterator, '/^.+' . $extension . '$/i', RecursiveRegexIterator::GET_MATCH);
    }

    /**
     * Finds all the translations keys present on $file and returns an array with them
     *
     * @param  string $file
     * @return array
     */
    protected function getTranslationKeys($file)
    {
        $translation_keys = null;
        preg_match_all('/__\((?:\'|\")(.*)(?:\'|\")\)/', file_get_contents($file), $translation_keys);
        return $translation_keys[1];
    }

    /**
     * Creates a json string containing the key => translation pairs for $locale
     *
     * @param  array $translation_keys
     * @param  string $locale
     * @return array
     */
    protected function createTranslationBundle($translation_keys, $locale)
    {
        $result = [];
        foreach ($translation_keys as $translation_key) {
            $result[$translation_key] = trans($translation_key);
        }
        return $result;

    }
    
    /**
     * Merges a list of given bundles by name and returns the array
     *
     * @param  mixed $bundles_names
     * @param  mixed $locale
     * @return array
     */
    protected function mergeBundles(array $bundle_names, string $locale) {
        $unmerged_bundles = [];
        foreach ($bundle_names as $bundle_name) {
            $bundle_file = $this->bundle_path . $locale . "\\" . $bundle_name . ".json";
            $unmerged_bundles[] = json_decode(file_get_contents($bundle_file), true);
        }
        return array_merge(...$unmerged_bundles);
    }

}
