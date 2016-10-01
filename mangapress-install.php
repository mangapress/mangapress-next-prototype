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


    private function __construct()
    {
        self::$version = strval( get_option('mangapress_next_ver') );
    }


    /**
     * Static function for plugin activation.
     *
     * @return void
     */
    public function do_activate()
    {
        if (version_compare(phpversion(), '5.3', '<')) {
            wp_die(__('This plugin requires PHP 5.3 or higher. If you\'re running php 5.2.x, please use Manga+Press 2.9.x.'));
        }

        $this->do_migrate();
        $this->do_install();
        $this->check_to_upgrade();

        MangaPress_Bootstrap::get_instance()->init();
        $this->after_plugin_activation();

        flush_rewrite_rules(false);
    }


    /**
     * Run install. Right now, simply involves adding new version and migrating old options to new
     */
    public function do_install()
    {

        if (self::$version == '') {
            self::$version = MP_VERSION;
            add_option('mangapress_next_ver', MP_VERSION, '', 'no');
            add_option('mangapress_next_options', serialize(MangaPress_Options::get_default_options()), '', 'no');
        }
    }


    /**
     * Migrate old Manga+Press Options
     */
    public function do_migrate()
    {
        // check for old version, retrieve if it exists
        if (get_option('mangapress_ver')) {
            $mp_options = get_option( 'mangapress_options' );

            add_option('mangapress_next_options', maybe_serialize(array_merge($mp_options, MangaPress_Options::get_default_options())));
            add_option('mangapress_next_ver', MP_VERSION);
            self::$version = MP_VERSION;

            delete_option('mangapress_ver');
            delete_option('mangapress_options');
        }
    }


    /**
     * Check version to see if upgrade is needed
     */
    public function check_to_upgrade()
    {
        if (version_compare(self::$version, MP_VERSION, '<')) {
            $this->do_upgrade();
        }
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
        delete_option('mangapress_upgrade');
        delete_option('mangapress_ver');

        flush_rewrite_rules(false);
    }


    /**
     * Get current version
     * @return string
     */
    public static function getVersion()
    {
        return self::$version;
    }


    /**
     * Set current version
     * @param string $ver
     */
    private static function setVersion($ver)
    {
        self::$version = $ver;
    }
}
