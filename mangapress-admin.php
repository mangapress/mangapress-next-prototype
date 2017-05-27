<?php
/**
 * MangaPress_Admin class. Controls page optio
 *
 * @package MangaPress_Next\MangaPress_Admin
 * @author Jess Green <jgreen at psy-dreamer.com>
 * @version $Id$
 * @license GPL
 */
final class MangaPress_Admin
{
    /**
     * Page slug constant
     *
     * @var string
     */
    const ADMIN_PAGE_SLUG = 'mangapress-options-page';


    /**
     * Options page hook. Used internally for enqueuing scripts/styles
     * that are specific to the page
     *
     * @var string
     */
    private static $mangapress_page_hook = null;


    /**
     * Initialize admin menus
     */
    public function __construct()
    {
        add_action('admin_menu', array(__CLASS__, 'admin_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
    }


    /**
     * Add options page
     */
    public static function admin_menu()
    {
        self::$mangapress_page_hook = add_options_page(
            __("Manga+Press Options", MP_DOMAIN),
            __("Manga+Press Options", MP_DOMAIN),
            'manage_options',
            self::ADMIN_PAGE_SLUG,
            array(__CLASS__, 'load_page')
        );
    }


    /**
     * Load options page. Fired by add_options_page
     */
    public static function load_page()
    {
        require MP_ABSPATH . '/includes/pages/options.php';
    }


    /**
     * Enqueue needed scripts for Manga+Press Options Page
     */
    public static function enqueue_scripts()
    {

    }
}