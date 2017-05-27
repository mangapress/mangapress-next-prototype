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
     * Get default options
     * @return array
     */
    public static function get_default_options()
    {
        $options = self::options_fields();
        $defaults = [];
        foreach ($options as $section => $option) {
            $defaults[$section] = [];
            foreach ($option as $option_name => $option_params) {
                if (!isset($option_params['default'])) continue;
                $defaults[$section][$option_name] = $option_params['default'];
            }
        }
        return $defaults;
    }


    /**
     * MangaPress_Options constructor.
     */
    public function __construct()
    {
        add_action('admin_init', array(__CLASS__, 'admin_init'));
    }


    /**
     * Initialize options
     */
    public static function admin_init()
    {
        if (defined('DOING_AJAX') && DOING_AJAX)
            return;

        register_setting(
            self::OPTIONS_GROUP_NAME,
            self::OPTIONS_GROUP_NAME,
            array(__CLASS__, 'sanitize_options')
        );
    }


    /**
     * Helper function for creating default options fields.
     *
     * @return array
     */
    public static function options_fields()
    {
        /*
         * Section
         *      |_ Option
         *              |_ Option Setting
         */
        $options = array(
            'basic' => array(
                'group_comics'      => array(
                    'id'    => 'group-comics',
                    'type'  => 'checkbox',
                    'title' => __('Group Comics', MP_DOMAIN),
                    'valid' => 'boolean',
                    'description' => __('Group comics by category. This option will ignore the parent category, and group according to the child-category.', MP_DOMAIN),
                    'default' => false,
                    'callback' => array(__CLASS__, 'settings_field_cb'),
                ),
                'group_by_parent'      => array(
                    'id'    => 'group-by-parent',
                    'type'  => 'checkbox',
                    'title' => __('Use Parent Category', MP_DOMAIN),
                    'valid' => 'boolean',
                    'description' => __('Group comics by top-most parent category. Use this option if you have sub-categories but want your navigation to function using the parent category.', MP_DOMAIN),
                    'default'     => false,
                    'callback'    => array(__CLASS__, 'settings_field_cb'),
                ),
                'latestcomic_page'  => array(
                    'id'    => 'latest-comic-page',
                    'type'  => 'select',
                    'title' => __('Latest Comic Page', MP_DOMAIN),
                    'value' => array(
                        'no_val' => __('Select a Page', MP_DOMAIN),
                    ),
                    'valid' => 'array',
                    'default'  => 0,
                    'callback' => array(__CLASS__, 'ft_basic_page_dropdowns_cb'),
                ),
                'comicarchive_page' => array(
                    'id'    => 'archive-page',
                    'type'  => 'select',
                    'title' => __('Comic Archive Page', MP_DOMAIN),
                    'value' => array(
                        'no_val' => __('Select a Page', MP_DOMAIN),
                    ),
                    'valid' => 'array',
                    'default' => 0,
                    'callback' => array(__CLASS__, 'ft_basic_page_dropdowns_cb'),
                ),
            ),
            'comic_page' => array(
                'generate_comic_page' => array(
                    'id'    => 'generate-page',
                    'type'  => 'checkbox',
                    'title'       => __('Generate Comic Page', MP_DOMAIN),
                    'description' => __('Generate a comic page based on values below.', MP_DOMAIN),
                    'valid'       => 'boolean',
                    'default'     => 1,
                    'callback' => array(__CLASS__, 'settings_field_cb'),
                ),
                'comic_page_width'    => array(
                    'id'    => 'page-width',
                    'type'  => 'text',
                    'title'   => __('Comic Page Width', MP_DOMAIN),
                    'valid'   => '/[0-9]/',
                    'default' => 600,
                    'callback' => array(__CLASS__, 'settings_field_cb'),
                ),
                'comic_page_height'   => array(
                    'id'    => 'page-height',
                    'type'  => 'text',
                    'title'   => __('Comic Page Height', MP_DOMAIN),
                    'valid'   => '/[0-9]/',
                    'default' => 1000,
                    'callback' => array(__CLASS__, 'settings_field_cb'),
                ),
            ),
            'nav' => array(
                'nav_css'    => array(
                    'id'     => 'navigation-css',
                    'title'  => __('Navigation CSS', MP_DOMAIN),
                    'description' => __('Turn this off. You know you want to!', MP_DOMAIN),
                    'type'   => 'select',
                    'value'  => array(
                        'custom_css' => __('Custom CSS', MP_DOMAIN),
                        'default_css' => __('Default CSS', MP_DOMAIN),
                    ),
                    'valid'   => 'array',
                    'default' => 'custom_css',
                    'callback' => array(__CLASS__, 'settings_field_cb'),
                ),
                'display_css' => array(
                    'id'       => 'display',
                    'callback' => array(__CLASS__, 'ft_navigation_css_display_cb'),
                )
            ),
        );
        return apply_filters('mangapress_options_fields', $options);
    }


    public static function settings_field_cb()
    {

    }


    public static function ft_navigation_css_display_cb()
    {

    }


    /**
     * Sanitize options before saving to database
     *
     * @param array $options Array of options to be sanitized
     * @return array
     */
    public static function sanitize_options($options)
    {
        return $options;
    }
}