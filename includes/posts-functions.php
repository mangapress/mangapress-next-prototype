<?php
/**
 * WordPress Filters and Actions functions
 * @package MangaPress_Next\Posts
 */
namespace MangaPress\Posts;

define('MP_CATEGORY_PARENTS', 1);
define('MP_CATEGORY_CHILDREN', 2);
define('MP_CATEGORY_ALL', 3);


/**
 * Meta box call-back function.
 *
 * @return void
 */
function meta_box_cb()
{
    add_meta_box(
        'comic-image',
        __('Comic Image', MP_DOMAIN),
        'MangaPress\Posts\comic_meta_box_cb',
        \MangaPress_Posts::POST_TYPE,
        'normal',
        'high'
    );

    /*
     * Because we don't need this...the comic image is the "Featured Image"
     * TODO add an option for users to override this "functionality"
     */
    remove_meta_box('postimagediv', 'mangapress_comic', 'side');
}


/**
 * Comic meta box
 *
 * @return void
 */
function comic_meta_box_cb()
{
    require_once MP_ABSPATH . 'includes/pages/meta-box-add-comic.php';
}


/**
 * Ajax hook for grabbing html
 */
function get_image_html_ajax()
{
    // TODO add nonce verification

    // get image
    $image_ID = filter_input(INPUT_POST, 'id') ? filter_input(INPUT_POST, 'id') : false;
    $action   = filter_input(INPUT_POST, 'action')
        ? filter_input(INPUT_POST, 'action') : \MangaPress_Posts::ACTION_REMOVE_IMAGE;

    header("Content-type: application/json");
    if ($action == \MangaPress_Posts::ACTION_GET_IMAGE_HTML){
        if ($image_ID) {
            echo json_encode(array('html' => get_image_html($image_ID),));
        }
    } else {
        echo json_encode(array('html' => get_remove_image_html(),));
    }

    die();
}


/**
 * Retrieve image html
 *
 * @param int $image_ID
 * @return string
 */
function get_image_html($image_ID)
{
    $image_html = wp_get_attachment_image($image_ID, 'medium');
    if ($image_html == '')
        return '';

    ob_start();
    require_once MP_ABSPATH . 'includes/pages/set-image-link.php';
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
}


/**
 * Reset comic image html
 *
 * @return string
 */
function get_remove_image_html()
{
    ob_start();
    require_once MP_ABSPATH . 'includes/pages/remove-image-link.php';
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
}


/**
 * Save post meta data. By default, Manga+Press uses the _thumbnail_id
 * meta key. This is the same meta key used for the post featured image.
 *
 * @param int $post_id
 * @param \WP_Post $post
 *
 * @return int
 */
function save_post($post_id, $post)
{
    if ($post->post_type !== \MangaPress_Posts::POST_TYPE || empty($_POST))
        return $post_id;

    if (!wp_verify_nonce(filter_input(INPUT_POST, '_insert_comic'), \MangaPress_Posts::NONCE_INSERT_COMIC))
        return $post_id;

    $image_ID = (int)filter_input(INPUT_POST, '_mangapress_comic_image', FILTER_SANITIZE_NUMBER_INT);
    if ($image_ID) {
        set_post_thumbnail($post_id, $image_ID);
    }

    set_post_terms($post_id);

    return $post_id;
}


/**
 * Set comic post terms
 *
 * @param int $post_id
 */
function set_post_terms($post_id)
{
    // if no terms have been assigned, assign the default
    if (!isset($_POST['tax_input'][\MangaPress_Posts::TAX_SERIES][0])
        || ($_POST['tax_input'][\MangaPress_Posts::TAX_SERIES][0] == 0
            && count($_POST['tax_input'][\MangaPress_Posts::TAX_SERIES]) == 1)) {

        $default_cat = get_option('mangapress_default_category');
        wp_set_post_terms($post_id, $default_cat, \MangaPress_Posts::TAX_SERIES);
    } else {
        // continue as normal
        wp_set_post_terms($post_id, $_POST['tax_input'][\MangaPress_Posts::TAX_SERIES], \MangaPress_Posts::TAX_SERIES);
    }
}


/**
 * Enqueue scripts for post-edit and post-add screens
 */
function enqueue_scripts()
{
    $current_screen = get_current_screen();

    if (!isset($current_screen->post_type) || !isset($current_screen->base))
        return;

    if (!($current_screen->post_type == \MangaPress_Posts::POST_TYPE
        && $current_screen->base == 'post'))
        return;

    // Include in admin_enqueue_scripts action hook
    wp_enqueue_media();
    wp_register_script(
        'mangapress-media-popup',
        MP_URLPATH . 'assets/scripts/add-comic.js',
        array( 'jquery' ),
        MP_VERSION,
        true
    );

    wp_localize_script(
        'mangapress-media-popup',
        MP_DOMAIN,
        array(
            'title'  => __('Upload or Choose Your Comic Image File', MP_DOMAIN),
            'button' => __('Insert Comic into Post', MP_DOMAIN),
        )
    );

    wp_enqueue_script('mangapress-media-popup');
}


/**
 * Modify header columns for Comic Post-type
 *
 * @global \WP_Post $post
 * @param array $column
 * @return void
 */
function comics_headers($column)
{
    global $post;

    if ("cb" == $column) {
        echo "<input type=\"checkbox\" value=\"{$post->ID}\" name=\"post[]\" />";
    } elseif ("thumbnail" == $column) {

        $thumbnail_html = get_the_post_thumbnail($post->ID, 'thumbnail', array('class' => 'wp-caption'));

        if ($thumbnail_html) {
            $edit_link = get_edit_post_link($post->ID, 'display');
            echo "<a href=\"{$edit_link}\">{$thumbnail_html}</a>";
        } else {
            echo "No image";
        }
    } elseif ("title" == $column) {
        echo $post->post_title;
    } elseif ("series" == $column) {
        $series = wp_get_object_terms( $post->ID, 'mangapress_series' );
        if (!empty($series)){
            $series_html = array();
            foreach ($series as $s)
                array_push($series_html, '<a href="' . get_term_link($s->slug, 'mangapress_series') . '">'.$s->name."</a>");

            echo implode($series_html, ", ");
        }
    } elseif ("post_date" == $column) {
        echo date( "Y/m/d", strtotime($post->post_date) );

    } elseif ("description" == $column) {
        echo $post->post_excerpt;
    } elseif ("author" == $column) {
        echo $post->post_author;
    }
}


/**
 * Modify comic columns for Comics screen
 *
 * @param array $columns
 * @return array
 */
function comics_columns($columns)
{

    $columns = array(
        "cb"          => "<input type=\"checkbox\" />",
        "thumbnail"   => __("Thumbnail", MP_DOMAIN),
        "title"       => __("Comic Title", MP_DOMAIN),
        "series"      => __("Series", MP_DOMAIN),
        "description" => __("Description", MP_DOMAIN),
    );

    return $columns;
}


/**
 * Retrieve term IDs. Either child-cats or parent-cats.
 *
 * @global \wpdb $wpdb
 * @param integer $object_ID Object ID
 * @param mixed $taxonomy Taxonomy name or array of names
 * @param integer $get Whether or not to get child-cats or top-level cats
 *
 * @return array
 */
function _get_object_terms($object_ID, $taxonomy, $get = MP_CATEGORY_PARENTS)
{
    global $wpdb;
    if ($get == MP_CATEGORY_PARENTS) {
        $parents = "AND tt.parent = 0";
    } else if ($get == MP_CATEGORY_CHILDREN) {
        $parents = "AND tt.parent != 0";
    } else {
        $parents = "";
    }
    $tax = (array) $taxonomy;
    $taxonomies = "'" . implode("', '", $tax) . "'";
    $query = "SELECT t.term_id FROM {$wpdb->terms} AS t "
        . "INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_id = t.term_id "
        . "INNER JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id "
        . "WHERE tt.taxonomy IN ({$taxonomies}) "
        . "AND tr.object_id IN ({$object_ID}) "
        . "{$parents} ORDER BY t.term_id ASC";
    return $wpdb->get_col($query);
}


/**
 * Clone of WordPress function get_boundary_post(). Retrieves first and last
 * comic posts.
 *
 * @TODO Work in $in_same_term and $group_by_parent parameters
 *
 * @param bool $in_same_term Optional. Whether returned post should be in same category.
 * @param bool $group_by_parent Optional. Whether returned post should be in the same parent category.
 * @param bool $start Optional. Whether to retrieve first or last post.
 * @param string $taxonomy Optional. Which taxonomy to pull from.
 *
 * @return \WP_Post|false Return a WP_Post object on success, false if no posts are found
 */
function get_boundary_post($in_same_term = false, $group_by_parent = false, $start = true, $taxonomy = 'category')
{
    $post = get_post();
    if (!is_single($post) || !mangapress_is_comic($post)) {
        return false;
    }

    $query_args = array(
        'post_type' => get_post_type($post),
        'posts_per_page' => 1,
        'orderby' => 'post_date', // TODO Make this configurable
        'order' => $start ? 'DESC' : 'ASC',
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false
    );

    $term_array = array();

    if ($in_same_term) {
        if (!$group_by_parent) {
            $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
        } else {
            $term_array = _get_object_terms($post->ID, $taxonomy);
        }

        $query_args['tax_query'] = [
            [
                'taxonomy' => $taxonomy,
                'terms' => $term_array,
                'include_children' => !$group_by_parent,
            ]
        ];
    }

    $start_post = get_posts($query_args);
    if (isset($start_post[0])) {
        return $start_post[0];
    }

    return false;
}


/**
 * Handles looking for previous and next comics.
 *
 * @TODO Work in $in_same_term and $group_by_parent parameters
 *
 * @param bool $in_same_term Optional. Whether returned post should be in same category.
 * @param bool $group_by_parent Optional. Whether to limit to category parent
 * @param bool $previous Optional. Whether to retrieve next or previous post.
 * @param string $taxonomy Optional. Which taxonomy to pull from.
 *
 * @return \WP_Post|false Return WP_Post object if successful, false if not
 */
function get_adjacent_post($in_same_term = false, $group_by_parent = false, $previous = true, $taxonomy = 'category')
{
    $post = get_post();
    if (!is_single($post) || !mangapress_is_comic($post)) {
        return false;
    }

    $current_post_date = $post->post_date;

    $order = $previous ? 'DESC' : 'ASC';
    $date_order = $previous ? 'before' : 'after';

    $args = [
        'post_type' => get_post_type($post),
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'order' => $order,
        'orderby' => 'post_date',
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false,
        'date_query' => [
            [
                'column' => 'post_date',
                $date_order => $current_post_date,
            ],
        ],
    ];

    if ($in_same_term) {
        if (!$group_by_parent) {
            $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
        } else {
            $term_array = _get_object_terms($post->ID, $taxonomy);
        }

        $args['tax_query'] = [
            [
                'taxonomy' => $taxonomy,
                'terms' => $term_array,
                'include_children' => !$group_by_parent,
            ]
        ];
    }

    $query = new \WP_Query($args);

    if (isset($query->post)) {
        return $query->post;
    }

    return false;
}