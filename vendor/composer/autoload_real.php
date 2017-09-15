<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit31fb905b1f4d5d8ff658f222801a3490 {

    private static $loader;

    public static function loadClassLoader($class) {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader() {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit31fb905b1f4d5d8ff658f222801a3490', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInit31fb905b1f4d5d8ff658f222801a3490', 'loadClassLoader'));

        $vendorDir = dirname(__DIR__);
        $baseDir = dirname($vendorDir);
        $useStaticLoader = 0;//PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION');
        if ($useStaticLoader) {
            require_once __DIR__ . '/autoload_static.php';
            call_user_func(\Composer\Autoload\ComposerAutoloaderInit31fb905b1f4d5d8ff658f222801a3490::getInitializer($loader));
        } else {
            $map = require __DIR__ . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                $loader->set($namespace, $path);
            }

            $classMap = require __DIR__ . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
            $map = require __DIR__ . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }
        }

        $loader->register(true);
        if ($useStaticLoader) {
            $includeFiles = Composer\Autoload\ComposerAutoloaderInit31fb905b1f4d5d8ff658f222801a3490::$files;
        } else {
            $includeFiles = require __DIR__ . '/autoload_files.php';
        }
        foreach ($includeFiles as $fileIdentifier => $file) {
            ComposerAutoloaderInit31fb905b1f4d5d8ff658f222801a3490($fileIdentifier, $file);
        }
        return $loader;
    }

}

function ComposerAutoloaderInit31fb905b1f4d5d8ff658f222801a3490($fileIdentifier, $file) {
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        require $file;

        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
    }
}
