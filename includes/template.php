<?php
/**
 * Template functions
 * @package MangaPress_Next
 */


/**
 * Checks if the current post is a comic post
 *
 * @return bool
 */
function mangapress_is_comic($post_id = false)
{
    if (!$post_id) {
        global $post;
    } else {
        $post = get_post($post_id);
    }

    return (get_post_type($post) == MangaPress_Posts::POST_TYPE);
}


function mangapress_nav()
{

}