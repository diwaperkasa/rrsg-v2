<?php

function message_box($atts, $content = null)
{

    return "
        <div class='message-box' style='" . (isset($atts['bg_color']) ? "background-color:{$atts['bg_color']};" : null) . (isset($atts['padding']) ? "padding-top:{$atts['padding']}px;padding-bottom:{$atts['padding']}px;" : null) . ";'>
            " . do_shortcode($content) . "
        </div>
    ";
}

add_shortcode("message_box", "message_box");

function row($atts, $content = null)
{
    return "<div class='row row-main'>" . do_shortcode($content) . "</div>";
} 

add_shortcode("row", "row");

function col($atts, $content = null)
{
    return "
        <div class='col" . ((isset($atts['span']) ? "-md-{$atts['span']} " : null)) . " col" . (isset($atts['span__sm']) ? "-sm-{$atts['span__sm']} " : null) . "'>
            <div class='col-inner'>
            " . do_shortcode($content) . "
            </div>
        </div>
    ";
} 

add_shortcode("col", "col");

function gap($atts, $content = null)
{
    return "<div style='height:" . (isset($atts["height"]) ? $atts["height"]  : "0px") . "'>" . do_shortcode($content) . "</div>";
}

add_shortcode("gap", "gap");

function row_inner($atts, $content = null)
{
    return "<div class='row " . (isset($atts['v_align']) ? "align-{$atts['v_align']} " : null) . (isset($atts['h_align']) ? "text-{$atts['h_align']} " : null) . "'>" . do_shortcode($content) . "</div>";
}

add_shortcode("row_inner", "row_inner");

function col_inner($atts, $content = null)
{
    return "<div class='col" . (isset($atts['span__sm']) ? "-sm-{$atts['span__sm']} " : null) . "'>" . do_shortcode($content) . "</div>";
}

add_shortcode("col_inner", "col_inner");

function button($atts, $content = null)
{
    return "
        <div class='text-center px-4'>
            <a " . (isset($atts['target']) ? "target={$atts['target']}" : null) . " href='{$atts['link']}' class='button btn primary w-100 bg-dark text-white " . (isset($atts['expand']) ? "expand" : null) . "' style='" . (isset($atts['radius']) ? "border-radius:{$atts['radius']}" : null) . "'>{$atts['text']}</a>
        </div>
    ";
}

add_shortcode("button", "button");

function divider($atts, $content = null)
{
    return "<hr class='bg-dark border-2 border-top border-dark mx-4' />";
}

add_shortcode("divider", "divider");

function subscribe_type($atts, $content = null)
{
    return "
        <select id='subscribe-type' class='form-control rounded-0' aria-label='Default select example'>
            <option value='single' data-url='https://robb-report-singapore.myshopify.com/products/i-robb-report-singapore-i-march-2021-issue'>Single Issue</option>
            <option value='local' data-url='https://robb-report-singapore.myshopify.com/products/i-robb-report-singapore-i-annual-print-subscription-12-issues-with-local-shipping'>Annual Print Subscription (Local)</option>
            <option value='international' data-url='https://robb-report-singapore.myshopify.com/products/i-robb-report-singapore-i-annual-print-subscription-12-issues-with-overseas-shipping'>Annual Print Subscription (International)</option>
            <option value='digital' data-url='https://www.magzter.com/SG/Indochine-Media-Pte-Ltd/Robb-Report-Singapore/Lifestyle'>Digital Subscription</option>
        </select>
    ";
}

add_shortcode("subscribe_type", "subscribe_type");