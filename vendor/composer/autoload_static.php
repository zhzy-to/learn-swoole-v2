<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc2f2e94c06de40f20bea0b5fc6a9ae04
{
    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'Zhzy\\Swow\\' => 10,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Zhzy\\Swow\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc2f2e94c06de40f20bea0b5fc6a9ae04::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc2f2e94c06de40f20bea0b5fc6a9ae04::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc2f2e94c06de40f20bea0b5fc6a9ae04::$classMap;

        }, null, ClassLoader::class);
    }
}
