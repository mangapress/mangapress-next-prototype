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
        $this->assertNotEquals(did_action('init'), 0);
    }

}