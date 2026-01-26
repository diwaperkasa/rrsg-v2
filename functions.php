<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('after_setup_theme', 'crb_load');
add_action('carbon_fields_register_fields', 'add_custom_field_action');

add_filter('acf/settings/save_json', 'my_acf_json_save_point');

function my_acf_json_save_point($path)
{

    $path = get_stylesheet_directory() . '/acf-json';

    return $path;
}

//Add Style
function theme_enqueue_styles()
{
    wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/assets/vendor/bootstrap/bootstrap.min.css');
    wp_enqueue_style('owl-carousel', get_stylesheet_directory_uri() . '/assets/vendor/owlcarousel/assets/owl.carousel.min.css');
    wp_enqueue_style('owl-carousel-theme', get_stylesheet_directory_uri() . '/assets/vendor/owlcarousel/assets/owl.theme.default.css', ['owl-carousel']);
    wp_enqueue_style('main-style', get_stylesheet_directory_uri() . '/assets/css/style.css?v=4.9');
}

add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

//Add Script
function theme_enqueue_script()
{
    wp_enqueue_script('jquery-rrsg', get_stylesheet_directory_uri() . '/assets/vendor/jquery/jquery-3.2.1.min.js', [], false, true);
    wp_enqueue_script('bootstrap', get_stylesheet_directory_uri() . '/assets/vendor/bootstrap/bootstrap.bundle.min.js', [], false, true);
    wp_enqueue_script('owl-carousel', get_stylesheet_directory_uri() . '/assets/vendor/owlcarousel/owl.carousel.min.js', [], false, true);
    wp_enqueue_script('main-script', get_stylesheet_directory_uri() . '/assets/js/main.js?v=1.5', [], false, true);
}

add_action('wp_enqueue_scripts', 'theme_enqueue_script');

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
    require_once(get_stylesheet_directory() . '/shortcode/functions.php');
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
            $output .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" alt="' . esc_attr(sprintf(__('View all posts in %s', 'textdomain'), $category->name)) . '"><span class="cat-' . $category->slug . '">' . esc_html($category->name) . '</span></a>';
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
    $templateData = [
        'dfpTarget' => get_targets(),
    ];

    if (is_front_page()) {
        $templateData['banner'] = get_slider();
    }

    if (is_single() || is_page()) {
        $context = \Timber\Timber::context($data);
        $templateData['data'] = $context;
    }
    
    if (is_search()) {
        $context['posts'] = Timber::get_posts([
            'post_status'           => 'publish',
            'ignore_sticky_posts'   => true,
            'fields'                => '',
            'post_type'             => ['post', 'features', 'package'],
            'ep_integrate'          => true,
            'suppress_filters'      => false,
        ]);

        $templateData['data'] = $context;
    }

    if (is_tag() || is_tax()) {
        $context['posts'] = Timber::get_posts([
            'ep_integrate'          => true,
            'suppress_filters'      => false,
        ]);

        $templateData['data'] = $context;
    }

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
        'orderby'                => 'date',
        'no_found_rows' => true,
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
        'no_found_rows' => true,
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
        'orderby'   => 'date',
        'no_found_rows' => true,
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
    $term = get_queried_object();
    $articles = get_article($term->slug, $limit);

    return $articles;
}

function get_most_popular_article()
{
    $posts_most_pageviews_id = rr_fetch_post_ids_popular();

    $args = [
        'post_type'           => ['post', 'features', 'package'],
        'post__in'            => $posts_most_pageviews_id,
        'orderby'             => 'date',
        'posts_per_page'      => '6',
        'no_found_rows' => true,
    ];

    $query = new WP_Query($args);
    wp_reset_postdata();

    return $query;
}

function get_current_url()
{
    global $wp;
    $current_url = add_query_arg(array(), $wp->request);
    $part = explode("/", $current_url);

    return current($part);
}

//Ajax Load More
function be_load_more_js()
{
    $term = get_queried_object();
    $query = array(
        'post__not_in'          => array(get_queried_object_id()),
        'posts_per_page'        => 5,
        'post_status'           => 'publish',
        'post_type'             => ['post', 'package', 'features'],
        'order'                 => 'DESC',
        'orderby'               => 'date',
        'no_found_rows' => true,
    );

    if ($term) {
        $query['tax_query'] = [
            [
                'taxonomy' => 'category',
                'terms' => $term->slug,
                'field' => 'slug',
            ]
        ];
    }

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

function rrsg_custom_post_gallery($value, $attr, $instance)
{
    $post = get_post();

    $html5 = current_theme_supports('html5', 'gallery');
    $atts  = shortcode_atts(
        array(
            'order'      => 'ASC',
            'orderby'    => 'menu_order ID',
            'id'         => $post ? $post->ID : 0,
            'itemtag'    => $html5 ? 'figure' : 'dl',
            'icontag'    => $html5 ? 'div' : 'dt',
            'captiontag' => $html5 ? 'figcaption' : 'dd',
            'columns'    => 3,
            'size'       => 'large',
            'include'    => '',
            'exclude'    => '',
            'link'       => '',
        ),
        $attr,
        'gallery'
    );

    $id = (int) $atts['id'];

    if (!empty($atts['include'])) {
        $_attachments = get_posts(
            array(
                'include'        => $atts['include'],
                'post_status'    => 'inherit',
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'order'          => $atts['order'],
                'orderby'        => $atts['orderby'],
            )
        );

        $attachments = array();
        foreach ($_attachments as $key => $val) {
            $attachments[$val->ID] = $_attachments[$key];
        }
    } elseif (!empty($atts['exclude'])) {
        $post_parent_id = $id;
        $attachments = get_children(
            array(
                'post_parent'    => $id,
                'exclude'        => $atts['exclude'],
                'post_status'    => 'inherit',
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'order'          => $atts['order'],
                'orderby'        => $atts['orderby'],
            )
        );
    } else {
        $post_parent_id = $id;
        $attachments = get_children(
            array(
                'post_parent'    => $id,
                'post_status'    => 'inherit',
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'order'          => $atts['order'],
                'orderby'        => $atts['orderby'],
            )
        );
    }

    if (!empty($post_parent_id)) {
        $post_parent = get_post($post_parent_id);

        // terminate the shortcode execution if user cannot read the post or password-protected
        if (
            (!is_post_publicly_viewable($post_parent->ID) && !current_user_can('read_post', $post_parent->ID))
            || post_password_required($post_parent)
        ) {
            return '';
        }
    }

    if (empty($attachments)) {
        return '';
    }

    if (is_feed()) {
        $output = "\n";
        foreach ($attachments as $att_id => $attachment) {
            if (!empty($atts['link'])) {
                if ('none' === $atts['link']) {
                    $output .= wp_get_attachment_image($att_id, $atts['size'], false, $attr);
                } else {
                    $output .= wp_get_attachment_link($att_id, $atts['size'], false);
                }
            } else {
                $output .= wp_get_attachment_link($att_id, $atts['size'], true);
            }
            $output .= "\n";
        }
        return $output;
    }

    $itemtag    = tag_escape($atts['itemtag']);
    $captiontag = tag_escape($atts['captiontag']);
    $icontag    = tag_escape($atts['icontag']);
    $valid_tags = wp_kses_allowed_html('post');
    if (!isset($valid_tags[$itemtag])) {
        $itemtag = 'dl';
    }
    if (!isset($valid_tags[$captiontag])) {
        $captiontag = 'dd';
    }
    if (!isset($valid_tags[$icontag])) {
        $icontag = 'dt';
    }

    $columns   = (int) $atts['columns'];
    $itemwidth = $columns > 0 ? floor(100 / $columns) : 100;
    $float     = is_rtl() ? 'right' : 'left';

    $selector = "gallery-{$instance}";

    $gallery_style = '';

    /**
     * Filters whether to print default gallery styles.
     *
     * @since 3.1.0
     *
     * @param bool $print Whether to print default gallery styles.
     *                    Defaults to false if the theme supports HTML5 galleries.
     *                    Otherwise, defaults to true.
     */
    if (apply_filters('use_default_gallery_style', !$html5)) {
        $type_attr = current_theme_supports('html5', 'style') ? '' : ' type="text/css"';

        $gallery_style = "
		<style{$type_attr}>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
			/* see gallery_shortcode() in wp-includes/media.php */
		</style>\n\t\t";
    }

    $size_class  = sanitize_html_class(is_array($atts['size']) ? implode('x', $atts['size']) : $atts['size']);
    $gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

    /**
     * Filters the default gallery shortcode CSS styles.
     *
     * @since 2.5.0
     *
     * @param string $gallery_style Default CSS styles and opening HTML div container
     *                              for the gallery shortcode output.
     */
    $output = apply_filters('gallery_style', $gallery_style . $gallery_div);

    if (!empty($attachments)) {
        $output .= "<div class='carousel js-flickity'>";

        foreach ($attachments as $id => $attachment) {
            $attr = (trim($attachment->post_excerpt)) ? array('aria-describedby' => "$selector-$id") : '';

            if (!empty($atts['link']) && 'file' === $atts['link']) {
                $image_output = wp_get_attachment_link($id, $atts['size'], false, false, false, $attr);
            } elseif (!empty($atts['link']) && 'none' === $atts['link']) {
                $image_output = wp_get_attachment_image($id, $atts['size'], false, $attr);
            } else {
                $image_output = wp_get_attachment_image($id, $atts['size'], false, false, false, $attr);
            }

            $image_meta = wp_get_attachment_metadata($id);

            $orientation = '';

            if (isset($image_meta['height'], $image_meta['width'])) {
                $orientation = ($image_meta['height'] > $image_meta['width']) ? 'portrait' : 'landscape';
            }

            $itemTemplate = "
                <figure {$itemtag} class='wp-caption carousel-cell {$orientation}'>
                    <div>
                        {$image_output}
                    </div>
                    <figcaption class='wp-caption-text' id='{$selector}-{$id}'>
                        " . wptexturize($attachment->post_excerpt) . "
                    </figcaption>
                </figure>
            ";

            $output .= $itemTemplate;
        }

        $output .= "</div>\n";
    }

    $output .= "</div>\n";

    return $output;
}

function theme_setup()
{
    /* add woocommerce support */
    add_theme_support('woocommerce');

    /* add title tag support */
    add_theme_support('title-tag');

    /* Add default posts and comments RSS feed links to head */
    add_theme_support('automatic-feed-links');

    /* Add excerpt to pages */
    add_post_type_support('page', 'excerpt');

    /* Add support for post thumbnails */
    add_theme_support('post-thumbnails');

    /* Add support for Selective Widget refresh */
    add_theme_support('customize-selective-refresh-widgets');

    /** Add sensei support */
    add_theme_support('sensei');

    /* Add support for HTML5 */
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'widgets',
    ));

    /*  Registrer menus. */
    register_nav_menus(array(
        'primary' => __('Main Menu', 'flatsome'),
        'primary_mobile' => __('Main Menu - Mobile', 'flatsome'),
        'footer' => __('Footer Menu', 'flatsome'),
        'top_bar_nav' => __('Top Bar Menu', 'flatsome'),
        'my_account' => __('My Account Menu', 'flatsome'),
    ));

    /*  Enable support for Post Formats */
    add_theme_support('post-formats', array('video'));
}

add_action('after_setup_theme', 'theme_setup');

function get_desktop_menu()
{
    $menu_name = 'primary-menu';
    $menu_list = '<ul id="desktop-menu" class="navbar-nav">' . "\n";

    if ($menu_items = wp_get_nav_menu_items($menu_name)) {
        $count = 0;
        $submenu = false;
        $parent_id = 0;
        $previous_item_has_submenu = false;

        foreach ((array) $menu_items as $key => $menu_item) {
            $title = $menu_item->title;
            $url = $menu_item->url;
            // check if it's a top-level item
            if ($menu_item->menu_item_parent == 0) {
                $parent_id = $menu_item->ID;
                // write the item but DON'T close the A or LI until we know if it has children!
                $menu_list .= "\t" . '<li class="nav-item dropdown"><a class="p-2 link-secondary text-uppercase dropdown-toggle" role="button" data-toggle="dropdown" aria-expanded="false" href="' . ($title === "Robb Spotlight" ? "javascript:void(0);" : $url) . '">' . $title;
            }
            // if this item has a (nonzero) parent ID, it's a second-level (child) item
            else {
                if (!$submenu) { // first item
                    // add the dropdown arrow to the parent
                    $menu_list .= '<span class="arrow-down"></span></a>' . "\n";
                    // start the child list
                    $submenu = true;
                    $previous_item_has_submenu = true;
                    $menu_list .= "\t\t" . '<ul class="dropdown-menu">' . "\n";
                }

                $menu_list .= "\t\t\t" . '<li>';
                $menu_list .= '<a class="dropdown-item text-uppercase" href="' . $url . '" class="title">' . $title . '</a>';
                $menu_list .= '</li>' . "\n";
                // if it's the last child, close the submenu code
                if (isset($menu_items[$count + 1]) && $menu_items[$count + 1]->menu_item_parent != $parent_id && $submenu) {
                    $menu_list .= "\t\t" . '</ul></li>' . "\n";
                    $submenu = false;
                }
            }
            // close the parent (top-level) item
            if (empty($menu_items[$count + 1]) || $menu_items[$count + 1]->menu_item_parent != $parent_id) {
                if ($previous_item_has_submenu) {
                    // the a link and list item were already closed
                    $previous_item_has_submenu = false; //reset
                } else {
                    // close a link and list item
                    $menu_list .= "\t" . '</a></li>' . "\n";
                }
            }

            $count++;
        }
    } else {
        $menu_list .= '<!-- no list defined -->';
    }

    $menu_list .= "\t" . '</ul>' . "\n";

    return $menu_list;
}

function get_mobile_menu()
{
    $menu_name = 'primary-menu';
    $menu_list = '<ul class="nav side-nav d-grid">' . "\n";

    if ($menu_items = wp_get_nav_menu_items($menu_name)) {
        $count = 0;
        $submenu = false;
        $parent_id = 0;
        $previous_item_has_submenu = false;

        foreach ((array) $menu_items as $key => $menu_item) {
            $title = $menu_item->title;
            $url = $menu_item->url;
            // check if it's a top-level item
            if ($menu_item->menu_item_parent == 0) {
                $parent_id = $menu_item->ID;
                // write the item but DON'T close the A or LI until we know if it has children!
                $menu_list .= "\t" . '
                    <li class="nav-item d-grid">
                        <div class="btn-group">
                            <a type="button" href="' . ($title === "Robb Spotlight" ? "javascript:void(0);" : $url) . '" class="btn fs-1 btn-light text-start bg-transparent border-0">' . $title . '</a>
                            <a type="button" class="d-none btn btn-light dropdown-toggle dropdown-toggle-split text-end bg-transparent border-0"
                                data-bs-toggle="collapse" data-bs-target="#submenu-' . $parent_id . '" aria-expanded="true" aria-controls="submenu-' . $parent_id . '">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </a>
                ';
            }
            // if this item has a (nonzero) parent ID, it's a second-level (child) item
            else {
                if (!$submenu) { // first item
                    // add the dropdown arrow to the parent
                    $menu_list .= '</div>' . "\n";
                    // start the child list
                    $submenu = true;
                    $previous_item_has_submenu = true;
                    $menu_list .= "\t\t" . '<ul aria-labelledby="submenu-' . $parent_id . '" id="submenu-' . $parent_id . '">' . "\n";
                }

                $menu_list .= "\t\t\t" . '<li class="list-unstyled">';
                $menu_list .= '<a class="nav-link fs-2" href="' . $url . '">' . $title . '</a>';
                $menu_list .= '</li>' . "\n";
                // if it's the last child, close the submenu code
                if (isset($menu_items[$count + 1]) && $menu_items[$count + 1]->menu_item_parent != $parent_id && $submenu) {
                    $menu_list .= "\t\t" . '</ul>' . "\n";
                    $submenu = false;
                }
            }
            // close the parent (top-level) item
            if (empty($menu_items[$count + 1]) || $menu_items[$count + 1]->menu_item_parent != $parent_id) {
                if ($previous_item_has_submenu) {
                    // the a link and list item were already closed
                    $previous_item_has_submenu = false; //reset
                } else {
                    // close a link and list item
                    $menu_list .= "\t" . '</div></li>' . "\n";
                }
            }

            $count++;
        }

        // $menu_list .= "\t" . '</ul></li>' . "\n";
    } else {
        $menu_list .= '<!-- no list defined -->';
    }

    $top_menu_item = get_top_menu();

    foreach ($top_menu_item as $top_menu) {
        $menu_list .= "\t" . '<li class="nav-item d-grid">';
        $menu_list .= '<div class="btn-group">';
        $menu_list .= '<a class="btn fs-1 btn-light text-start bg-transparent border-0" href="' . $top_menu->url . '">' . $top_menu->title . '</a>';
        $menu_list .= '</div></li>' . "\n";
    }

    $menu_list .= "\t" . '</ul>' . "\n";

    return $menu_list;
}

function get_slider_menu()
{
    $menu_name = 'primary-menu';
    $menu_list = '';
    $sub_menu_list = '';

    if ($menu_items = wp_get_nav_menu_items($menu_name)) {
        $count = 0;
        $submenu = false;
        $parent_id = 0;
        $previous_item_has_submenu = false;
        $menu_list = '<div class="slider-menu">' . "\n";

        foreach ((array) $menu_items as $key => $menu_item) {
            $title = $menu_item->title;
            $url = $menu_item->url;
            // check if it's a top-level item
            if ($menu_item->menu_item_parent == 0) {
                $parent_id = $menu_item->ID;
                // write the item but DON'T close the A or LI until we know if it has children!
                $menu_list .= "\t" . '
                    <div class="btn-group">
                        <a class="p-2 link-secondary text-uppercase fs-6" href="' . ($title === "Robb Spotlight" ? "javascript:void(0);" : $url) . '">' . $title . '</a>
                        <button type="button" class="d-none btn btn-secondary dropdown-toggle dropdown-toggle-split bg-transparent border-0 text-dark" type="button" data-sub-menu-id="submenu-' . $parent_id . '">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                    </div>
                ';
            }
            // if this item has a (nonzero) parent ID, it's a second-level (child) item
            else {
                if (!$submenu) {
                    // first item
                    // add the dropdown arrow to the parent
                    // $menu_list .= '</div>' . "\n";
                    // start the child list
                    $submenu = true;
                    $previous_item_has_submenu = true;
                    $sub_menu_list .= "\t\t" . '
                        <ul class="dropdown-menu" id="submenu-' . $parent_id . '">' . "\n";
                }

                $sub_menu_list .= "\t\t\t" . '<li>';
                $sub_menu_list .= '<a class="dropdown-item text-uppercase" href="' . $url . '">' . $title . '</a>';
                $sub_menu_list .= '</li>' . "\n";
                // if it's the last child, close the submenu code
                if (isset($menu_items[$count + 1]) && $menu_items[$count + 1]->menu_item_parent != $parent_id && $submenu) {
                    $sub_menu_list .= "\t\t" . '</li></ul>' . "\n";
                    $submenu = false;
                }
            }

            $count++;
        }

        $menu_list .= "\t" . '</div>' . "\n";
    } else {
        $menu_list .= '<!-- no list defined -->';
    }

    return [
        'menu' => $menu_list,
        'sub_menu' => $sub_menu_list
    ];
}

function get_top_menu()
{
    $menu_name = 'top-menu';
    $menu_items = wp_get_nav_menu_items($menu_name);

    return $menu_items ?: [];
}

function get_footer_menu()
{
    $menu_name = 'footer-menu';
    $menu_items = wp_get_nav_menu_items($menu_name);

    return $menu_items ?: [];
}

if (function_exists('acf_add_options_page')) {
	acf_add_options_page();
}

add_filter('ep_is_integrated', '__return_true');

add_action( 'pre_get_posts', function( $query ) {
    if ($query->is_search() && $query->is_main_query()) {
        $query->set('post_status', ['publish']);
        $query->set('ignore_sticky_posts', true);
        
        if ( apply_filters('ep_is_integrated', false) ) {
            $query->set('fields', '');
            $query->set('ep_integrate', true);
            $query->set('suppress_filters', false);
        }
    }
});

add_filter('ep_is_integrated', function ($integrated) {
    if (is_admin()) {
        return false;
    }

    return $integrated;
}, 10, 3);