<?php
/**
 * Class SampleTest
 *
 * @package Mangapress_Next
 */

/**
 * Sample test case.
 */
class PluginActivationTest extends WP_UnitTestCase {

    private $mangapressInstall;

    private $user;

    public function setUp()
    {
        $this->user = $this->factory()->user->create(array('user_login' => 'admin', 'user_pass' => 'admin'));

        $this->mangapressInstall = MangaPress_Install::get_instance();
        $this->mangapressInstall->do_activate();
    }

    public function test_wp_debug()
    {
        $this->assertTrue(WP_DEBUG);
    }

    public function test_version() {
        $this->assertTrue((MP_VERSION == '0.0.1'));
    }

    public function test_version_on_activation() {
        $this->assertEquals(MP_VERSION, MangaPress_Install::getVersion());
    }

    public function test_default_options() {
        $this->assertNotEmpty(unserialize(get_option('mangapress_options')));
    }
}
