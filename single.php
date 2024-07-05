<?php

add_filter( 'post_gallery', 'rrsg_custom_post_gallery' , 10, 3);

echo rrsg_render('single.twig');