<?php
/**
 * MangaPress_Options class. Controls registering, fields, and sanitizing routines for
 * plugin options
 *
 * @package MangaPress_Next\MangaPress_Options
 * @author Jess Green <jgreen at psy-dreamer.com>
 * @version $Id$
 * @license GPL
 */
final class MangaPress_Options
{
    const OPTIONS_GROUP_NAME = 'mangapress_options';

    /**
     * Default options array
     *
     * @var array
     */
    protected static $default_options = array(
        'basic' => array(
            'group_comics' => 0,
            'group_by_parent' => 0,
            'latestcomic_page' => 0,
            'comicarchive_page' => 0,
        ),
        'comic_page' => array(
            'generate_comic_page' => 0,
            'comic_page_width' => 600,
            'comic_page_height' => 1000,
        ),
        'nav' => array(
            'nav_css' => 'custom_css',
        ),
    );


    /**
     * Get default options
     * @return array
     */
    public static function get_default_options()
    {
        return self::$default_options;
    }


    /**
     * MangaPress_Options constructor.
     */
    public function __construct()
    {
        add_action('admin_init', array('MangaPress_Options', 'admin_init'));
    }


    /**
     * @
     */
    public static function admin_init()
    {

    }
}