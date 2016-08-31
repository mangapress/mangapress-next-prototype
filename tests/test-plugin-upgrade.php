<?php
/**
 * Class PluginUpgradeTest
 *
 * @package Mangapress_Next
 */

/**
 * Test fresh install of Manga+Press NEXT
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
        $this->mangapressInstall = MangaPress_Install::get_instance();
        $this->mangapressInstall->do_activate();
    }


    public function tearDown()
    {
        $this->mangapressInstall->do_deactivate();
    }


    public function test_upgrade()
    {
        $this->assertTrue(did_action('mangapress_upgrade'));
    }
}