<?php
/**
 * @package MangaPress_Next
 * @version $Id$
 * @author Jessica Green <jgreen@psy-dreamer.com>
 */

/**
 * MangaPress Posts class
 * Handles functionality for the Comic post-type
 *
 * @package MangaPress_Next
 * @subpackage MangaPress_Posts
 * @author Jessica Green <jgreen@psy-dreamer.com>
 */
class MangaPress_Posts
{
    /**
     * Get image html
     *
     * @var string
     */
    const ACTION_GET_IMAGE_HTML = 'mangapress-get-image-html';


    /**
     * Remove image html and return Add Image string
     *
     * @var string
     */
    const ACTION_REMOVE_IMAGE = 'mangapress-remove-image';


    /**
     * Nonce string
     *
     * @var string
     */
    const NONCE_INSERT_COMIC = 'mangapress_comic-insert-comic';


    /**
     * Post-type name
     *
     * @var string
     */
    const POST_TYPE = 'mangapress_comic';


    /**
     * Taxonomy name for Series
     *
     * @var string
     */
    const TAX_SERIES = 'mangapress_series';


    /**
     * Default archive date format
     *
     * @var string
     */
    const COMIC_ARCHIVE_DATEFORMAT = 'm.d.Y';


    /**
     * Post-type Slug. Defaults to comic.
     */
    const SLUG = 'comic';


    private static $instance = null;

    /**
     * Class for initializing custom post-type
     *
     * @var MangaPress_PostType
     */
    private $_post_type = null;


    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->register_content_types();
        $this->rewrite_rules();
    }


    /**
     * Register needed content-types
     */
    private function register_content_types()
    {
        register_taxonomy(self::TAX_SERIES, array(self::POST_TYPE), array(
            'label' => __('Series', MP_DOMAIN),
            'singular_name' => __('Series', MP_DOMAIN),
            'hierarchical' => true,
            'query_var'    => 'series',
            'rewrite'      => array(
                'slug' => 'series'
            ),
        ));

        register_post_type(self::POST_TYPE, array(
            'label'    => __('Comics', MP_DOMAIN),
            'singular_name'    => __('Comic', MP_DOMAIN),
            'supports'      => array(
                'title',
                'comments',
                'thumbnails',
                'publicize',
            ),
            'register_meta_box_cb' => array($this, 'meta_box_cb'),
            'menu_icon' => null,
            'rewrite'   => array(
                'slug' => self::SLUG,
            ),
            'taxonomies' => array(
                self::TAX_SERIES,
            ),
        ));
    }


    /**
     * Add new rewrite rules for Comic post-type
     */
    private function rewrite_rules()
    {
        $post_type = self::POST_TYPE;
        $slug      = self::SLUG;

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$",
            'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$",
            'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$",
            'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$",
            'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$",
            'index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$",
            'index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$",
            'index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/([0-9]{1,2})/?$",
            'index.php?year=$matches[1]&monthnum=$matches[2]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$",
            'index.php?year=$matches[1]&feed=$matches[2]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$",
            'index.php?year=$matches[1]&feed=$matches[2]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/page/?([0-9]{1,})/?$",
            'index.php?year=$matches[1]&paged=$matches[2]&post_type=' .  $post_type,
            'top'
        );

        add_rewrite_rule(
            "{$slug}/([0-9]{4})/?$",
            'index.php?year=$matches[1]&post_type=' .  $post_type,
            'top'
        );
    }


    /**
     * Meta box call-back function.
     *
     * @return void
     */
    public function meta_box_cb()
    {
        add_meta_box(
            'comic-image',
            __('Comic Image', MP_DOMAIN),
            array($this, 'comic_meta_box_cb'),
            self::POST_TYPE,
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
    public function comic_meta_box_cb()
    {
        require_once MP_ABSPATH . 'includes/pages/meta-box-add-comic.php';
    }

}