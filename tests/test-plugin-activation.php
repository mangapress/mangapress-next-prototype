<?php
/**
 * Class PluginActivationTest
 *
 * @package Mangapress_Next
 */

/**
 * Test fresh install of Manga+Press NEXT
 */
class PluginActivationTest extends WP_UnitTestCase
{


    /**
     * @var \MangaPress_Install
     */
    private $mangapressInstall;


    /**
     * Setup before tests
     */
    public function setUp()
    {
        $this->mangapressInstall = MangaPress_Install::get_instance();
        $this->mangapressInstall->do_activate();
    }


    public function tearDown()
    {
        $this->mangapressInstall->do_deactivate();
    }


    /**
     * Test for WP_DEBUG
     */
    public function test_wp_debug()
    {
        $this->assertTrue(WP_DEBUG);
    }


    /**
     * Test MP_VERSION
     */
    public function test_version()
    {
        $this->assertNotEmpty(MP_VERSION);
    }


    /**
     * Test version after activation
     */
    public function test_version_on_activation()
    {
        $this->assertEquals(MP_VERSION, MangaPress_Install::getVersion());
    }


    /**
     * Test if default options have been loaded
     */
    public function test_default_options()
    {
        $this->assertNotEmpty(unserialize(get_option('mangapress_next_options')));
    }


    /**
     * Test if mangapress_after_plugin_activation fired
     */
    public function test_mangapress_after_plugin_activation()
    {
        $this->assertNotFalse(did_action('mangapress_after_plugin_activation'));
    }


    /**
     * Check for existence of mangapress_default_category
     */
    public function test_mangapress_default_category()
    {
        $this->assertNotFalse(get_option('mangapress_default_category'));
    }
}
