<?php
/**
 * Plugin bootstrap class.
 *
 * @package MangaPress
 * @subpackage MangaPress_Bootstrap
 * @author Jess Green <jgreen@psy-dreamer.com>
 */
class MangaPress_Bootstrap
{


    /**
     * Options array
     *
     * @var array
     */
    protected $_options;


    /**
     * Instance of MangaPress_Bootstrap
     *
     * @var MangaPress_Bootstrap
     */
    protected static $_instance;


    /**
     * MangaPress Posts object
     *
     * @var \MangaPress_Posts
     */
    protected $_posts_helper;


    /**
     * Options helper object
     *
     * @var \MangaPress_Options
     */
    protected $_options_helper;


    /**
     * Static function used to initialize Bootstrap
     *
     * @return void
     */
    public static function load_plugin()
    {
        self::get_instance();
    }


    /**
     * Get instance of MangaPress_Bootstrap
     *
     * @return MangaPress_Bootstrap
     */
    public static function get_instance()
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /**
     * PHP5 constructor method
     */
    protected function __construct()
    {
        load_plugin_textdomain(MP_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

        add_action('init', array($this, 'init'), 500);
    }


    /**
     * Run init functionality
     *
     * @see init() hook
     * @return void
     */
    public function init()
    {
        $this->_posts_helper   = new MangaPress_Posts();
        $this->_options_helper = new MangaPress_Options();
    }
}
