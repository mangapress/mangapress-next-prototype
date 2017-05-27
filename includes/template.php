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
    $next_post  = get_adjacent_post($group, $by_parent, 'mangapress_series', false, false);
    $prev_post  = get_adjacent_post($group, $by_parent, 'mangapress_series', false, true);
    $last_post  = get_boundary_post($group, $by_parent, 'mangapress_series', false, false);
    $first_post = get_boundary_post($group, $by_parent, 'mangapress_series', false, true);
    $current_page = $post->ID; // use post ID this time.
}