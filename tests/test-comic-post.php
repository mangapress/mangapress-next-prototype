<?php
/**
 * Class PluginActivationTest
 *
 * @package Mangapress_Next
 */

/**
 * Comic post creation in Manga+Press NEXT
 */
class ComicPostTest extends WP_UnitTestCase
{


    /**
     * @var \MangaPress_Install
     */
    private $mangapressInstall;

    public function setUp()
    {
        $this->mangapressInstall = MangaPress_Install::get_instance();
        $this->mangapressInstall->do_activate();

        parent::setUp();
    }


    public function tearDown()
    {
        $this->mangapressInstall->do_deactivate();
        parent::tearDown();
    }


    public function test_post_type_public()
    {
        global $wp_post_types;

        $this->assertTrue($wp_post_types[MangaPress_Posts::POST_TYPE]->public);
    }


    public function test_post_save()
    {
        $post_id = $this->factory()->post->create(array(
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_title' => 'Test Comic',
        ));

        $this->assertNotEquals(did_action('save_post_mangapress_comic'), 0);
    }


    public function test_post_taxonomy_add_on_save()
    {
        $post_id = $this->factory()->post->create(array(
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_title' => 'Test Comic',
        ));

        $tax = wp_get_post_terms($post_id, MangaPress_Posts::TAX_SERIES);
        $this->assertEmpty($tax);
        $default_cat = false;
        if (empty($tax)) {
            $default_cat = get_option('mangapress_default_category');
            $this->assertNotFalse($default_cat);

            wp_set_post_terms($post_id, $default_cat, MangaPress_Posts::TAX_SERIES);
        }

        $tax = wp_get_post_terms($post_id, MangaPress_Posts::TAX_SERIES);
        $this->assertNotEmpty($tax);
    }


    public function test_comic_navigation()
    {
        // create a bunch of posts
        $this->factory()->post->create_many(10, array('post_type' => MangaPress_Posts::POST_TYPE));
    }

}