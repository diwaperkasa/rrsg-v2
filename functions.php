<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'after_setup_theme', 'crb_load' );
add_action( 'carbon_fields_register_fields', 'add_custom_field_action' );

function crb_load()
{
    require_once( __DIR__ . '/Lib/vendor/autoload.php' );
    // Initialize Carbon
    \Carbon_Fields\Carbon_Fields::boot();
    // Initialize Timber.
    \Timber\Timber::init();

    add_filter('timber/loader/twig', 'timber_environment');
}

function timber_environment(\Twig\Environment $twig)
{
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
    $twig->setLoader($loader);

    return $twig;
}

function add_custom_field_action()
{
}

function rrsg_render(string $path, array $data = [])
{
    $context = \Timber\Timber::context($data);

    return \Timber\Timber::compile("page/{$path}", $context);
}