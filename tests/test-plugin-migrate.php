<?php
/**
 * Class PluginMigrateTest
 *
 * @package Mangapress_Next
 */

/**
 * Test new install of Manga+Press NEXT over old version of Manga+Press
 */
class PluginMigrateTest extends WP_UnitTestCase
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
        add_option('mangapress_ver', '2.9.3');
        add_option('mangapress_options', MangaPress_Options::get_default_options());
        $this->mangapressInstall = MangaPress_Install::get_instance();
        $this->mangapressInstall->do_activate();
    }


    /**
     * Remove options created by setUp
     */
    public function tearDown()
    {
        delete_option('mangapress_ver');
        $this->mangapressInstall->do_deactivate();
    }


    public function test_migrate()
    {
        $this->assertNotEmpty(get_option('mangapress_next_options'));
        $this->assertEquals(get_option('mangapress_next_ver'), MP_VERSION);

        $this->assertFalse(get_option('mangapress_ver'));
        $this->assertFalse(get_option('mangapress_options'));
    }
}
