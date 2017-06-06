<?php
/**
 * Template functions. These functions live in the global namespace to maintain
 * continuity with WordPress template function naming standards.
 *
 * @package MangaPress_Next\Templates
 */
use MangaPress\Posts as Posts;

/**
 * Checks if the current post is a comic post
 *
 * @param int $post_id ID of the post being checked. Defaults to false
 *
 * @global \WP_Post $post Global WordPress post object. Used if no $post_id is passed
 *
 * @return bool
 */
function mangapress_is_comic($post_id = 0)
{
    if (!$post_id) {
        global $post;
    } else {
        $post = get_post($post_id);
    }

    return (get_post_type($post) == MangaPress_Posts::POST_TYPE);
}


/**
 * Get navigation for current post object.
 *
 * @global \WP_Post $post
 *
 * @param array $args Arguments for navigation output
 *
 * @return string Returns navigation string if $echo is set to false.
 */
function mangapress_get_comic_navigation($args = [])
{
    global $post;

    $defaults = array(
        'container'      => 'nav',
        'container_attr' => array(
            'id'    => 'comic-navigation',
            'class' => 'comic-nav-hlist-wrapper',
        ),
        'items_wrap'     => '<ul%1$s>%2$s</ul>',
        'items_wrap_attr' => array('class' => 'comic-nav-hlist'),
        'link_wrap'      => 'li',
        'link_before'    => '',
        'link_after'     => '',
    );
    $parsed_args = wp_parse_args($args, $defaults);
    $args = (object) $parsed_args;

    $navigation_links = Posts\get_adjacent_and_boundary_posts($post);
    $comic_nav = "";
    $show_container = false;
    if ( $args->container ) {
        $show_container = true;
        $attr           = "";
        if (!empty($args->container_attr)) {
            $attr_arr = [];
            foreach ($args->container_attr as $name => $value) {
                $attr_arr[] = "{$name}=\"" . esc_attr($value) . "\"";
            }
            $attr = " " . implode(" ", $attr_arr);
        }
        $comic_nav .= "<{$args->container}$attr>";
    }

    $items_wrap_attr = "";
    if (!empty($args->items_wrap_attr)) {
        $items_attr_arr = [];
        foreach ($args->items_wrap_attr as $name => $value) {
            $items_attr_arr[] = "{$name}=\"" . esc_attr($value) . "\"";
        }
        $items_wrap_attr = " " . implode(" ", $items_attr_arr);
    }

    $items = [];
    foreach ($navigation_links as $label => $link) {
        $items[] = "<{$args->link_wrap} class=\"link-{$label}\">\r\n"
                . ( !Posts\is_current_post($link['post'], $post)
                    ? "\t<a href=\"{$link['url']}\">{$link['label']}</a>\r\n"
                    : "\t<span class=\"comic-nav-span\">{$link['label']}</span>\r\n"
                )
                . "</{$args->link_wrap}>\r\n";
    }

    $items_str = implode(" ", apply_filters( 'mangapress_comic_navigation_items', $items, $args ));
    $comic_nav .= sprintf( $args->items_wrap, $items_wrap_attr, $items_str );
    if ($show_container){
        $comic_nav .= "</{$args->container}>";
    }

    return $comic_nav;
}


/**
 * Display navigation for current post object.
 *
 * @global \WP_Post $post
 *
 * @param array $args Arguments for navigation output
 */
function mangapress_comic_navigation($args = [])
{
    echo mangapress_get_comic_navigation($args);
}

/**
 * CPT-neutral Clone of WordPress' get_calendar
 *
 * @param bool $initial
 * @param bool $echo
 * @return mixed|void
 */
function mangapress_get_calendar($initial = true, $echo = true)
{
    global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;
    $key = md5( $m . $monthnum . $year );
    if ( $cache = wp_cache_get( 'mangapress_get_calendar', 'calendar' ) ) {
        if ( is_array($cache) && isset( $cache[ $key ] ) ) {
            if ( $echo ) {
                /**
                 * Filter the HTML calendar output.
                 *
                 * @since 2.9.0
                 *
                 * @param string $calendar_output HTML output of the calendar.
                 */
                echo apply_filters( 'mangapress_get_calendar', $cache[$key] );
                return;
            } else {
                /** This filter is documented in wp-includes/general-template.php */
                return apply_filters( 'mangapress_get_calendar', $cache[$key] );
            }
        }
    }
    if ( !is_array($cache) )
        $cache = array();
    // Quick check. If we have no posts at all, abort!
    if ( !$posts ) {
        $gotsome = $wpdb->get_var("SELECT 1 as test FROM $wpdb->posts WHERE post_type = '" . MangaPress_Posts::POST_TYPE . "' AND post_status = 'publish' LIMIT 1");
        if ( !$gotsome ) {
            $cache[ $key ] = '';
            wp_cache_set( 'mangapress_get_calendar', $cache, 'mangapress_calendar' );
            return;
        }
    }
    if ( isset($_GET['w']) )
        $w = ''.intval($_GET['w']);
    // week_begins = 0 stands for Sunday
    $week_begins = intval(get_option('start_of_week'));
    // Let's figure out when we are
    if ( !empty($monthnum) && !empty($year) ) {
        $thismonth = ''.zeroise(intval($monthnum), 2);
        $thisyear = ''.intval($year);
    } elseif ( !empty($w) ) {
        // We need to get the month from MySQL
        $thisyear = ''.intval(substr($m, 0, 4));
        $d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
        $thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
    } elseif ( !empty($m) ) {
        $thisyear = ''.intval(substr($m, 0, 4));
        if ( strlen($m) < 6 )
            $thismonth = '01';
        else
            $thismonth = ''.zeroise(intval(substr($m, 4, 2)), 2);
    } else {
        $thisyear = gmdate('Y', current_time('timestamp'));
        $thismonth = gmdate('m', current_time('timestamp'));
    }
    $unixmonth = mktime(0, 0 , 0, $thismonth, 1, $thisyear);
    $last_day = date('t', $unixmonth);
    // Get the next and previous month and year with at least one post
    $previous = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date < '$thisyear-$thismonth-01'
		AND post_type = '" . MangaPress_Posts::POST_TYPE . "' AND post_status = 'publish'
			ORDER BY post_date DESC
			LIMIT 1");
    $next = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:59'
		AND post_type = 'post' AND post_status = 'publish'
			ORDER BY post_date ASC
			LIMIT 1");
    /* translators: Calendar caption: 1: month name, 2: 4-digit year */
    $calendar_caption = _x('%1$s %2$s', 'calendar caption');
    $calendar_output = '<table id="manga-press-calendar">
	<caption>' . sprintf($calendar_caption, $wp_locale->get_month($thismonth), date('Y', $unixmonth)) . '</caption>
	<thead>
	<tr>';
    $myweek = array();
    for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
        $myweek[] = $wp_locale->get_weekday(($wdcount+$week_begins)%7);
    }
    foreach ( $myweek as $wd ) {
        $day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
        $wd = esc_attr($wd);
        $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
    }
    add_filter('month_link', 'mangapress_month_link', 10, 3);
    $calendar_output .= '
	</tr>
	</thead>
	<tfoot>
	<tr>';
    if ( $previous ) {
        $calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . get_month_link($previous->year, $previous->month) . '">&laquo; ' . $wp_locale->get_month_abbrev($wp_locale->get_month($previous->month)) . '</a></td>';
    } else {
        $calendar_output .= "\n\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
    }
    $calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';
    if ( $next ) {
        $calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . get_month_link($next->year, $next->month) . '">' . $wp_locale->get_month_abbrev($wp_locale->get_month($next->month)) . ' &raquo;</a></td>';
    } else {
        $calendar_output .= "\n\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
    }
    remove_filter('month_link', 'mangapress_month_link');
    $calendar_output .= '
	</tr>
	</tfoot>
	<tbody>
	<tr>';
    // Get days with posts
    $dayswithposts = $wpdb->get_results("SELECT DISTINCT DAYOFMONTH(post_date)
		FROM $wpdb->posts WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00'
		AND post_type = '" . MangaPress_Posts::POST_TYPE . "' AND post_status = 'publish'
		AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'", ARRAY_N);
    if ( $dayswithposts ) {
        foreach ( (array) $dayswithposts as $daywith ) {
            $daywithpost[] = $daywith[0];
        }
    } else {
        $daywithpost = array();
    }
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'camino') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false)
        $ak_title_separator = "\n";
    else
        $ak_title_separator = ', ';
    $ak_titles_for_day = array();
    $ak_post_titles = $wpdb->get_results("SELECT ID, post_title, DAYOFMONTH(post_date) as dom "
        ."FROM $wpdb->posts "
        ."WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00' "
        ."AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59' "
        ."AND post_type = '" . MangaPress_Posts::POST_TYPE . "' AND post_status = 'publish'"
    );
    if ( $ak_post_titles ) {
        foreach ( (array) $ak_post_titles as $ak_post_title ) {
            /** This filter is documented in wp-includes/post-template.php */
            $post_title = esc_attr( apply_filters( 'the_title', $ak_post_title->post_title, $ak_post_title->ID ) );
            if ( empty($ak_titles_for_day['day_'.$ak_post_title->dom]) )
                $ak_titles_for_day['day_'.$ak_post_title->dom] = '';
            if ( empty($ak_titles_for_day["$ak_post_title->dom"]) ) // first one
                $ak_titles_for_day["$ak_post_title->dom"] = $post_title;
            else
                $ak_titles_for_day["$ak_post_title->dom"] .= $ak_title_separator . $post_title;
        }
    }
    // See how much we should pad in the beginning
    $pad = calendar_week_mod(date('w', $unixmonth)-$week_begins);
    if ( 0 != $pad )
        $calendar_output .= "\n\t\t".'<td colspan="'. esc_attr($pad) .'" class="pad">&nbsp;</td>';
    $daysinmonth = intval(date('t', $unixmonth));
    for ( $day = 1; $day <= $daysinmonth; ++$day ) {
        if ( isset($newrow) && $newrow )
            $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
        $newrow = false;
        if ( $day == gmdate('j', current_time('timestamp')) && $thismonth == gmdate('m', current_time('timestamp')) && $thisyear == gmdate('Y', current_time('timestamp')) )
            $calendar_output .= '<td id="today">';
        else
            $calendar_output .= '<td>';
        if ( in_array($day, $daywithpost) ) { // any posts today?
            add_filter('day_link', 'mangapress_day_link', 10, 4);
            $calendar_output .= '<a href="' . get_day_link( $thisyear, $thismonth, $day ) . '" title="' . esc_attr( $ak_titles_for_day[ $day ] ) . "\">$day</a>";
            remove_filter('day_link', 'mangapress_day_link');
        } else
            $calendar_output .= $day;
        $calendar_output .= '</td>';
        if ( 6 == calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins) )
            $newrow = true;
    }
    $pad = 7 - calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins);
    if ( $pad != 0 && $pad != 7 )
        $calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';
    $calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";
    $cache[ $key ] = $calendar_output;
    wp_cache_set( 'mangapress_get_calendar', $cache, 'mangapress_calendar' );
    if ( $echo ) {
        /**
         * Filter the HTML calendar output.
         *
         * @since 2.9
         *
         * @param string $calendar_output HTML output of the calendar.
         */
        echo apply_filters( 'mangapress_get_calendar', $calendar_output );
    } else {
        return apply_filters( 'mangapress_get_calendar', $calendar_output );
    }
}


/**
 * Purge Manga+Press' calendar cache. Based on delete_get_calendar_cache()
 *
 * @see mangapress_get_calendar
 * @since 2.9
 */
function mangapress_delete_get_calendar_cache()
{
    wp_cache_delete( 'mangapress_get_calendar', 'mangapress_calendar' );
}
add_action( 'save_post_mangapress_comic', 'mangapress_delete_get_calendar_cache' );
add_action( 'delete_post', 'mangapress_delete_get_calendar_cache' );
add_action( 'update_option_start_of_week', 'mangapress_delete_get_calendar_cache' );
add_action( 'update_option_gmt_offset', 'mangapress_delete_get_calendar_cache' );