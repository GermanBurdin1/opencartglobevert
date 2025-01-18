<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6c715234cc5e2c853b6507d401531ce1
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
        'G' => 
        array (
            'Germa\\OpencartGlobevert\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'Germa\\OpencartGlobevert\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6c715234cc5e2c853b6507d401531ce1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6c715234cc5e2c853b6507d401531ce1::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6c715234cc5e2c853b6507d401531ce1::$classMap;

        }, null, ClassLoader::class);
    }
}