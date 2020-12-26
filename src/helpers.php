<?php
if (!function_exists('translate_attributes')) {    
    /**
     * Return the json string of $bundle
     *
     * @param  mixed $bundle
     * @return string
     */
    function json_bundle_translations($bundle)
    {
        $path = base_path() . "\\resources\\simple-translate\\bundles\\" . App::getLocale() . "\\$bundle.json";
        return file_get_contents($path);
    }
}
