<?php

class I18n {
    private static $language = 'en';
    private static $translations = [];
    
    public static function init($lang = 'en') {
        self::$language = $lang;
        self::loadTranslations();
    }
    
    private static function loadTranslations() {
        $file = __DIR__ . '/../lang/' . self::$language . '.php';
        if (file_exists($file)) {
            self::$translations = require $file;
        }
    }
    
    public static function t($key, $params = []) {
        $text = self::$translations[$key] ?? $key;
        
        foreach ($params as $param => $value) {
            $text = str_replace('{' . $param . '}', $value, $text);
        }
        
        return $text;
    }
    
    public static function setLanguage($lang) {
        self::$language = $lang;
        self::loadTranslations();
    }
    
    public static function getLanguage() {
        return self::$language;
    }
}

function __($key, $params = []) {
    return I18n::t($key, $params);
}
