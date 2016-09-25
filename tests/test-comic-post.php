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

    public function setUp()
    {
        parent::setUp();
    }


    public function tearDown()
    {
        $this->remove_added_uploads();
        parent::tearDown();
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
    }
}