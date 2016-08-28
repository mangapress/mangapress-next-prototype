<?php
/**
 * MangaPress Installation Class
 *
 * @package MangaPress
 * @author Jess Green <jgreen@psy-dreamer.com>
 * @version $Id$
 */
/**
 * @subpackage MangaPress_Install
 * @author Jess Green <jgreen@psy-dreamer.com>
 * @version $Id$
 */
class MangaPress_Install
{


    /**
     * Current MangaPress DB version
     *
     * @var string
     */
    protected static $version = '';


    /**
     * What type is the object? Activation, deactivation or upgrade?
     *
     * @var string
     */
    protected $type;


    /**
     * Instance of Bootstrap class
     * @var \MangaPress_Bootstrap
     */
    protected $bootstrap;


    /**
     * Instance of MangaPress_Install
     * @var \MangaPress_Install
     */
    protected static $instance;


    /**
     * Get instance of
     *
     * @return MangaPress_Install
     */
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Static function for plugin activation.
     *
     * @return void
     */
    public function do_activate()
    {
        self::$version = strval( get_option('mangapress_next_ver') );

        if (self::$version == '') {
            add_option( 'mangapress_next_ver', MP_VERSION, '', 'no');
            add_option( 'mangapress_options', serialize( MangaPress_Options::get_default_options() ), '', 'no' );
        }

        if (version_compare(self::$version, MP_VERSION, '<')
            && !(self::$version == '')) {
            add_option( 'mangapress_upgrade', 'yes', '', 'no');
        }

        MangaPress_Bootstrap::get_instance()->init();
        $this->after_plugin_activation();

        flush_rewrite_rules(false);
    }




    /**
     * Run routines after plugin has been activated
     *
     * @todo check for existing terms in Series
     *
     * @return void
     */
    public function after_plugin_activation()
    {
        /**
         * mangapress_after_plugin_activation
         * Allow other plugins to add to Manga+Press' activation sequence.
         *
         * @return void
         */
        do_action('mangapress_after_plugin_activation');


        // if the option already exists, exit
        if (get_option('mangapress_default_category')) {
            return;
        }

        // create a default series category
        $term = wp_insert_term(
            'Default Series',
            MangaPress_Posts::TAX_SERIES,
            array(
                'description' => __('Default Series category created when plugin is activated. It is suggested that you rename this category.', MP_DOMAIN),
                'slug'        => 'default-series',
            )
        );

        if (!($term instanceof WP_Error)) {
            add_option('mangapress_default_category', $term['term_id'], '', 'no');
        }
    }


    /**
     * Static function for plugin deactivation.
     *
     * @return void
     */
    public function do_deactivate()
    {
        delete_option('rewrite_rules');
        flush_rewrite_rules(false);
    }

    /**
     * Static function for upgrade
     *
     * @return void
     */
    public function do_upgrade()
    {
        do_action('mangapress_upgrade');

        update_option('mangapress_next_ver', MP_VERSION);
        delete_option( 'mangapress_upgrade' );
        flush_rewrite_rules(false);
    }


    public static function getVersion()
    {
        return self::$version;
    }
}
