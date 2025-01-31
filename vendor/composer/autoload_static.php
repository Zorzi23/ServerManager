<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1ffc9efde1ae2205a151bd263a6c935e
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'ServerManager\\' => 14,
        ),
        'R' => 
        array (
            'RegexFlow\\' => 10,
        ),
        'O' => 
        array (
            'ObjectFlow\\' => 11,
        ),
        'H' => 
        array (
            'HttpFlow\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ServerManager\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'RegexFlow\\' => 
        array (
            0 => __DIR__ . '/..' . '/zorzi23/regex_flow/src',
        ),
        'ObjectFlow\\' => 
        array (
            0 => __DIR__ . '/..' . '/zorzi23/object_flow/src',
        ),
        'HttpFlow\\' => 
        array (
            0 => __DIR__ . '/..' . '/zorzi23/http_flow/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1ffc9efde1ae2205a151bd263a6c935e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1ffc9efde1ae2205a151bd263a6c935e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit1ffc9efde1ae2205a151bd263a6c935e::$classMap;

        }, null, ClassLoader::class);
    }
}
