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

        $this->mangapressInstall = MangaPress_Install::get_instance();
        $this->mangapressInstall->do_activate();
    }


    /**
     * Remove options created by setUp
     */
    public function tearDown()
    {
        $this->mangapressInstall->do_deactivate();
    }

}
