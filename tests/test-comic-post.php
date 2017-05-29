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

        $user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
        $user = wp_set_current_user( $user_id );

        // This is the key here.
        set_current_screen( 'edit-post' );

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


    /**
     *
     */
    public function test_comic_navigation()
    {
        $this->factory()->post->create_many(15,
            [
                'post_author' => get_current_user_id(),
                'post_type' => MangaPress_Posts::POST_TYPE,
                'tax_input' => [
                    MangaPress_Posts::TAX_SERIES => [2]
                ]
            ]);

        $comics = get_posts([
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);

        global $wp_query, $post;
        $wp_query = new WP_Query([
            'p' => 13,
            'post_type' => MangaPress_Posts::POST_TYPE,
        ]);

        $post = $wp_query->get_queried_object();
        setup_postdata($post);
        $this->assertEquals(is_single(), true);

        $this->assertEquals(get_post() instanceof WP_Post, true);
        $this->assertEquals(taxonomy_exists(MangaPress_Posts::TAX_SERIES), true);

        $start = MangaPress\Posts\get_boundary_post(false, false, true, MangaPress_Posts::TAX_SERIES);
        $last = MangaPress\Posts\get_boundary_post(false, false, false, MangaPress_Posts::TAX_SERIES);
        $next = MangaPress\Posts\get_adjacent_post(false, false, false, MangaPress_Posts::TAX_SERIES);
        $prev = MangaPress\Posts\get_adjacent_post(false, false, true, MangaPress_Posts::TAX_SERIES);

        $this->assertInstanceOf(WP_Post::class, $start);
        $this->assertInstanceOf(WP_Post::class, $last);
        $this->assertInstanceOf(WP_Post::class, $next);
        $this->assertInstanceOf(WP_Post::class, $prev);

        $this->assertEquals($comics[0], $start, "get_boundary_post should match the first element returned by get_posts");
        $this->assertEquals($comics[count($comics) - 1], $last, "get_boundary_post should match the last element returned by get_posts");
//        $this->assertEquals($comics[count($comics) - 1], $last, "get_boundary_post should match the last element returned by get_posts");
//        $this->assertEquals($comics[count($comics) - 1], $last, "get_boundary_post should match the last element returned by get_posts");
    }
}