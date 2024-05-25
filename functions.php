<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('after_setup_theme', 'crb_load');
add_action('carbon_fields_register_fields', 'add_custom_field_action');

function crb_load()
{
    require_once(__DIR__ . '/Lib/vendor/autoload.php');
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
    $templateData = [
        'data' => $context,
        'dfpTarget' => get_targets(),
    ];

    return \Timber\Timber::compile("page/{$path}", $templateData);
}

// DFP Tags
function get_targets()
{
    global $post;
    $targets = [];

    if (is_home() || is_front_page() || is_page(array('newsletter', 'print-and-digital-subscription'))) {
        $targets[] = 'home';
    } elseif (is_singular(array('post', 'features'))) {
        $categories = wp_get_object_terms($post->ID, 'category');
        if (!empty($categories)) {
            foreach ($categories as $category)
                $targets[] = $category->slug;
        }
        $targets[] = array_push($targets, "$post->ID");
    } elseif (is_author()) {
        $targets[] = 'home';
    } elseif (is_category()) {
        $term = get_queried_object();
        if ($term->parent > 0) {
            $term      = get_term_by('id', $term->parent, 'category');
            $targets[] = $term->slug;
        } else {
            $targets[] = $term->slug;
        }
    }

    return $targets;
}
