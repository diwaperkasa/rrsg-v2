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
    add_filter('timber/twig', 'timber_add_function');

    require_once(get_stylesheet_directory() . '/google/api_rr_analytics.php');
}

function timber_add_function(\Twig\Environment $twig)
{
    $function = new \Twig\TwigFunction('short_title', 'short_title');
    $twig->addFunction($function);
    $function = new \Twig\TwigFunction('get_author_photo', 'get_author_photo');
    $twig->addFunction($function);
    $function = new \Twig\TwigFunction('parent_category_link', 'parent_category_link');
    $twig->addFunction($function);

    return $twig;
}

function short_title($post)
{
    $short_title = get_field('short_title', $post->ID);

    if (!empty($short_title)) {
        /* echo ucfirst(strtolower($short_title)); */
        return $short_title;
    } else {
        /* echo ucfirst(strtolower($post->post_title)); */
        return $post->post_title;
    }
}

function primary_category($post)
{
    $categories = get_the_category($post->ID);
    $output = '';
    $i = 0;

    foreach ($categories as $category) {
        if ($category->parent == 0 && $category->slug != 'business' && $category->slug != 'leisure') {
            if ($i != 0) break;
            $output .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" alt="' . esc_attr(sprintf(__('View all posts in %s', 'textdomain'), $category->name)) . '"><span class="cat-' . $category->slug . '">' . esc_html($category->name) . '</span></a>' . $separator;
            $i++;
        }
    }

    return trim($output);
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
        'banner' => get_slider(),
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

function get_banner()
{
    $today = date('Ymd');

    $banner_args = [
        'post_type'              => 'banner',
        'nopaging'               => false,
        'posts_per_page'         => '10',
        'order'                  => 'DESC',
        'orderby'                => 'ID',
        'meta_query'              => [
            [
                'key'        => 'banner_start_date',
                'compare'    => '<=',
                'value'        => $today,
            ],
            [
                'key'        => 'banner_end_date',
                'compare'    => '>=',
                'value'        => $today,
            ]
        ],
    ];
    $banners = new WP_Query($banner_args);
    wp_reset_postdata();

    return $banners;
}

function get_slider()
{
    $articles = get_field('articles');
    $postcount = get_field('how_many_articles_to_show');

    $args = array(
        'post_type'              => array('post', 'features', 'package'),
        'post_status'            => 'publish',
        'post__in'                 => $articles,
        'posts_per_page'         => $postcount,
        'order'                  => 'DESC',
        'orderby'                => 'post__in',
    );
    $query = new WP_Query($args);
    wp_reset_postdata();

    return $query;
}

function get_author_photo($post)
{
    $term_list = wp_get_post_terms($post->ID, 'writer', array('fields' => 'all'));
    $user_photo = get_field('photo', 'term_' . $term_list[0]->term_id);

    return $user_photo;
}

function parent_category_link($post)
{
    if (has_category('business', $post->ID)) {
        $category_name = 'Business';
        $category_id = get_cat_ID($category_name);
        $category_link = get_category_link($category_id);

        return esc_url($category_link);
    } elseif (has_category('leisure', $post->ID)) {
        $category_name = 'Leisure';
        $category_id = get_cat_ID($category_name);
        $category_link = get_category_link($category_id);

        return esc_url($category_link);
    }
}

function get_popular_article($post)
{
    $tags = wp_get_post_tags($post->ID);
    $tag_ids = array();
    $category = get_the_category();
    $category_id = $category[0]->cat_ID;

    foreach ($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;

    $args = [
        'category__in' => $category_id,
        'tag__in' => $tag_ids,
        'post__not_in' => [$post->ID],
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'orderby' => 'rand'
    ];

    $loop = new WP_Query($args);
    wp_reset_postdata();

    return $loop;
}

function get_latest_article()
{
    $args = array(
        'post_type'              => array('post', 'package', 'features'),
        'nopaging'               => false,
        'posts_per_page'         => '6',
        'order'                  => 'DESC',
        'orderby'                => 'date',
    );
    $query = new WP_Query($args);
    wp_reset_postdata();

    return $query;
}

function get_article(string $category, int $limit = 5, int $page = 1)
{
    $args = [
        'posts_per_page' => $limit,
        'paged' => $page,
        'post_status' => 'publish',
        'post_type' => ['post', 'package', 'features'],
        'order' => 'DESC',
        'orderby'   => 'post__in',
        'tax_query' => [
            [
                'taxonomy' => 'category',
                'terms' => $category,
                'field' => 'slug',
            ]
        ]
    ];

    $query = new WP_Query($args);
    wp_reset_postdata();

    return $query;
}

function get_category_article(int $limit = 5)
{
    global $wp;

    $current_slug = add_query_arg(array(), $wp->request);
    $articles = get_article($current_slug, $limit);

    return $articles;
}

function get_most_popular_article()
{
    $posts_most_pageviews_id = rr_fetch_post_ids_popular();

    $args = [
        'post_type'           => ['post', 'features', 'package'],
        'post__in'            => $posts_most_pageviews_id,
        'orderby'             => 'post__in',
        'posts_per_page'      => '6',
    ];

    $query = new WP_Query($args);
    wp_reset_postdata();

    return $query;
}

function get_current_url()
{
    global $wp;
    $current_url = add_query_arg( array(), $wp->request );
    $part = explode("/", $current_url);

    return current($part);
}

//Ajax Load More
function be_load_more_js()
{
    global $wp;

    $current_slug = add_query_arg(array(), $wp->request);
    $query = array(
        'post__not_in'          => array(get_queried_object_id()),
        'posts_per_page'        => 6,
        'post_status'           => 'publish',
        'post_type'             => ['post', 'package', 'features'],
        'order'                 => 'DESC',
        'orderby'               => 'post__in',
        'tax_query' => [
            [
                'taxonomy' => 'category',
                'terms' => $current_slug,
                'field' => 'slug',
            ]
        ]
    );

    $args = array(
        'nonce' => wp_create_nonce('be-load-more-nonce'),
        'url'   => admin_url('admin-ajax.php'),
        'query' => $query,
    );

    wp_enqueue_script('be-load-more', get_stylesheet_directory_uri() . '/assets/js/load-more.js', array('jquery'));
    wp_localize_script('be-load-more', 'beloadmore', $args);
}
add_action('wp_enqueue_scripts', 'be_load_more_js');
/**
 * AJAX Load More
 *
 */
function be_ajax_load_more()
{
    $args     = (isset($_POST['query'])) ? $_POST['query'] : [];
    $query = new WP_Query($args);
    wp_reset_postdata();

    $templateData = [
        'articles' => $query,
    ];

    $data = rrsg_render('component/post-item.twig', $templateData);
    wp_send_json_success($data);
    wp_die();
}

add_action('wp_ajax_be_ajax_load_more', 'be_ajax_load_more');
add_action('wp_ajax_nopriv_be_ajax_load_more', 'be_ajax_load_more');
