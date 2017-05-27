<?php
/**
 * Class PluginOptionsTest
 *
 * @package Mangapress_Next
 */

/**
 * Test Manga+Press NEXT options
 */
class PluginOptionsTest extends WP_UnitTestCase
{

    public function testOptionsExist()
    {
        $options = MangaPress_Options::options_fields();
        $this->assertEquals(is_array($options), true);
    }
}