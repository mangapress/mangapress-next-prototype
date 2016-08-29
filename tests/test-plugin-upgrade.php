<?php
/**
 * Class PluginUpgradeTest
 *
 * @package Mangapress_Next
 */

/**
 * Test new install of Manga+Press NEXT over old version of Manga+Press
 */
class PluginUpgradeTest extends WP_UnitTestCase
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


    /**
     * Test for existence (and value) of mangapress_upgrade
     */
    public function test_upgrade()
    {
        $current_old_version = get_option('mangapress_ver');

        // old version should not equal the current about-to-be-installed version
        // nor should it be empty
        $this->assertNotEquals($current_old_version, MP_VERSION);
        $this->assertNotEmpty($current_old_version);
        $this->assertNotFalse(did_action('mangapress_upgrade'));

        // upgrade option should be created
        $this->assertEquals(get_option('mangapress_upgrade'), 'yes');
    }
}
