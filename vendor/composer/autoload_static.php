<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf8d9b2def40e2de420a5ba0eed318293
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'szn\\rbac\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'szn\\rbac\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf8d9b2def40e2de420a5ba0eed318293::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf8d9b2def40e2de420a5ba0eed318293::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}