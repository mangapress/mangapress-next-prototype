<?php
/**
 * Class PluginLoadTest
 *
 * @package Mangapress_Next
 */

/**
 * Test new install of Manga+Press NEXT over old version of Manga+Press
 */
class PluginLoadTest extends WP_UnitTestCase
{

    public function setUp()
    {
        MangaPress_Bootstrap::load_plugin();
    }


    public function test_init()
    {
        global $wp_post_types, $wp_taxonomies;

        $this->assertNotEquals(did_action('init'), 0);
        $this->assertTrue(in_array(MangaPress_Posts::POST_TYPE, array_keys($wp_post_types)));
        $this->assertTrue(in_array(MangaPress_Posts::TAX_SERIES, array_keys($wp_taxonomies)));
    }

}