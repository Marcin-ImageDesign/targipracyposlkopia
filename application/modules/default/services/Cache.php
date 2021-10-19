<?php

class Service_Cache
{
    public static $deleted_files = [];
    public static $error_files = [];
    /**
     * @var Zend_Cache_Manager
     */
    private static $_cacheManager;

    /**
     * @param string $name
     *
     * @return Zend_Cache_Core
     */
    public static function getCache($name = 'default')
    {
        if (null === self::$_cacheManager) {
            self::$_cacheManager = self::getCacheManager();
        }

        return self::$_cacheManager->getCache($name);
    }

    public static function setCacheManager($cacheManager)
    {
        self::$_cacheManager = $cacheManager;
    }

    /**
     * @return Zend_Cache_Manager
     */
    public static function getCacheManager()
    {
        if (null === self::$_cacheManager) {
            throw new Exception('CacheManager not set!');
        }

        return self::$_cacheManager;
    }

    public static function clean()
    {
        $dir = self::getCache()->getBackend()->getOption('cache_dir');
        self::$deleted_files = [];
        self::$error_files = [];

        self::_clearDir($dir);
    }

    private static function _clearDir($dir)
    {
        $disable_files = ['.', '..', '.empty', 'managed', 'pagetag'];
        foreach (scandir($dir) as $file) {
            if (in_array($file, $disable_files, true)) {
                continue;
            }
            $fullname = rtrim($dir, DS) . DS . trim($file, DS);

            if (is_dir($fullname)) {
                self::_clearDir($fullname);
            } elseif (@unlink($fullname)) {
                self::$deleted_files[] = str_replace(ROOT_PATH, null, $fullname);
            } else {
                self::$error_files[] = str_replace(ROOT_PATH, null, $fullname);
            }
        }
    }
}
