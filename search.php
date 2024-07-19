<?php

$articles = [];

while ( have_posts() )
{
    the_post();
    $articles[] = get_post();
}

$data = [
    'articles' => $articles
];

echo rrsg_render('search.twig', $data);