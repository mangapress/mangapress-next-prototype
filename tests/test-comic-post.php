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
     * Test comic navigation — default term assigned
     */
    public function test_comic_navigation()
    {
        for ($c = -16; $c < 1; $c++) {
            $this->factory()->post->create(
                [
                    'post_author' => get_current_user_id(),
                    'post_type' => MangaPress_Posts::POST_TYPE,
                    'post_date' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_gmt' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified_gmt'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'tax_input' => [
                        MangaPress_Posts::TAX_SERIES => [2]
                    ]
                ]
            );
        }

        $comics = array_reverse(get_posts([
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'posts_per_page' => -1
        ]));

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

        $comic_index = array_search($post->post_title, array_column($comics, 'post_title'));

        $this->assertInstanceOf(WP_Post::class, $start, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $last, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $next, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $prev, "Instance returned should be of WP_Post");

        $this->assertEquals($comics[count($comics) - 1], $start, "get_boundary_post should match the first element returned by get_posts");
        $this->assertEquals($comics[0], $last, "get_boundary_post should match the last element returned by get_posts");

        $this->assertEquals($comics[$comic_index], $post);
        $this->assertEquals($comics[$comic_index - 1], $prev, "get_adjacent_post should match the element previous to the current element");
        $this->assertEquals($comics[$comic_index + 1], $next, "get_adjacent_post should match the element after the current element");
    }


    public function test_comic_navigation_with_taxonomy()
    {
        $results = $this->factory()->term->create_many(5, ['taxonomy' => MangaPress_Posts::TAX_SERIES]);
        for ($c = -16; $c < -8; $c++) {
            $this->factory()->post->create(
                [
                    'post_author' => get_current_user_id(),
                    'post_type' => MangaPress_Posts::POST_TYPE,
                    'post_date' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_gmt' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified_gmt'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'tax_input' => [
                        MangaPress_Posts::TAX_SERIES => $results[0]
                    ]
                ]
            );
        }

        for ($c = -8; $c < 1; $c++) {
            $this->factory()->post->create(
                [
                    'post_author' => get_current_user_id(),
                    'post_type' => MangaPress_Posts::POST_TYPE,
                    'post_date' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_gmt' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified_gmt'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'tax_input' => [
                        MangaPress_Posts::TAX_SERIES => $results[3]
                    ]
                ]
            );
        }

        $comics = array_reverse(get_posts([
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'posts_per_page' => -1,
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => MangaPress_Posts::TAX_SERIES,
                    'field' => 'term_id',
                    'terms' => [$results[0]]
                ]
            ]
        ]));

        $post_id = $comics[ intval( count($comics) / 2) ]->ID;
        global $wp_query, $post;
        $wp_query = new WP_Query([
            'p' => $post_id,
            'post_type' => MangaPress_Posts::POST_TYPE,
        ]);

        $post = $wp_query->get_queried_object();
        setup_postdata($post);
        $this->assertEquals(is_single(), true);
        $this->assertEquals(get_post() instanceof WP_Post, true);
        $this->assertEquals(taxonomy_exists(MangaPress_Posts::TAX_SERIES), true);

        $start = MangaPress\Posts\get_boundary_post(true, false, true, MangaPress_Posts::TAX_SERIES);
        $last = MangaPress\Posts\get_boundary_post(true, false, false, MangaPress_Posts::TAX_SERIES);
        $next = MangaPress\Posts\get_adjacent_post(true, false, false, MangaPress_Posts::TAX_SERIES);
        $prev = MangaPress\Posts\get_adjacent_post(true, false, true, MangaPress_Posts::TAX_SERIES);

        $comic_index = array_search($post->post_title, array_column($comics, 'post_title'));

        $this->assertInstanceOf(WP_Post::class, $start, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $last, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $next, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $prev, "Instance returned should be of WP_Post");

        $this->assertEquals($comics[count($comics) - 1], $start, "get_boundary_post should match the first element returned by get_posts");
        $this->assertEquals($comics[0], $last, "get_boundary_post should match the last element returned by get_posts");

        $this->assertEquals($comics[$comic_index], $post);
        $this->assertEquals($comics[$comic_index - 1], $prev, "get_adjacent_post should match the element previous to the current element");
        $this->assertEquals($comics[$comic_index + 1], $next, "get_adjacent_post should match the element after the current element");
    }


    public function test_comic_navigation_with_parent_taxonomy()
    {
        $parent_results = $this->factory()->term->create_many(2,
            [
                'taxonomy' => MangaPress_Posts::TAX_SERIES
            ]
        );

        $child_term_1 = $this->factory()->term->create(
            [
                'taxonomy' => MangaPress_Posts::TAX_SERIES,
                [
                    'parent' => $parent_results[0]
                ]
            ]
        );

        $child_term_2 = $this->factory()->term->create(
            [
                'taxonomy' => MangaPress_Posts::TAX_SERIES,
                [
                    'parent' => $parent_results[0]
                ]
            ]
        );

        for ($c = -16; $c < -8; $c++) {
            $this->factory()->post->create(
                [
                    'post_author' => get_current_user_id(),
                    'post_type' => MangaPress_Posts::POST_TYPE,
                    'post_date' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_gmt' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified_gmt'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'tax_input' => [
                        MangaPress_Posts::TAX_SERIES => [
                            $parent_results[0],
                            $child_term_1,
                        ]
                    ]
                ]
            );
        }

        for ($c = -8; $c < 1; $c++) {
            $this->factory()->post->create(
                [
                    'post_author' => get_current_user_id(),
                    'post_type' => MangaPress_Posts::POST_TYPE,
                    'post_date' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_gmt' => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'post_date_modified_gmt'  => date('Y-m-d H:i:s', strtotime("{$c} days")),
                    'tax_input' => [
                        MangaPress_Posts::TAX_SERIES => [
                            $parent_results[0],
                            $child_term_2,
                        ]
                    ]
                ]
            );
        }

        $comics_term_1 = array_reverse(get_posts([
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'posts_per_page' => -1,
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => MangaPress_Posts::TAX_SERIES,
                    'field' => 'term_id',
                    'terms' => [$child_term_1]
                ]
            ]
        ]));
        $comics_term_2 = array_reverse(get_posts([
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'posts_per_page' => -1,
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => MangaPress_Posts::TAX_SERIES,
                    'field' => 'term_id',
                    'terms' => [$child_term_2]
                ]
            ]
        ]));
        $comics_parent_term = array_reverse(get_posts([
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'posts_per_page' => -1,
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => MangaPress_Posts::TAX_SERIES,
                    'field' => 'term_id',
                    'terms' => [$parent_results[0]]
                ]
            ]
        ]));

        $this->assertEquals(count($comics_term_1), 8);
        $this->assertEquals(count($comics_term_2), 9);
        $this->assertEquals(count($comics_parent_term), 17);
        $comics = $comics_parent_term;

        $post_id = $comics[ intval( count($comics) / 2) ]->ID;
        global $wp_query, $post;
        $wp_query = new WP_Query([
            'p' => $post_id,
            'post_type' => MangaPress_Posts::POST_TYPE,
        ]);

        $post = $wp_query->get_queried_object();
        setup_postdata($post);
        $this->assertEquals(is_single(), true);
        $this->assertEquals(get_post() instanceof WP_Post, true);

        $start = MangaPress\Posts\get_boundary_post(true, true, true, MangaPress_Posts::TAX_SERIES);
        $last = MangaPress\Posts\get_boundary_post(true, true, false, MangaPress_Posts::TAX_SERIES);
        $next = MangaPress\Posts\get_adjacent_post(true, true, false, MangaPress_Posts::TAX_SERIES);
        $prev = MangaPress\Posts\get_adjacent_post(true, true, true, MangaPress_Posts::TAX_SERIES);

        $comic_index = array_search($post->post_title, array_column($comics, 'post_title'));

        $this->assertInstanceOf(WP_Post::class, $start, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $last, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $next, "Instance returned should be of WP_Post");
        $this->assertInstanceOf(WP_Post::class, $prev, "Instance returned should be of WP_Post");

        $this->assertEquals($comics[count($comics) - 1], $start, "get_boundary_post should match the first element returned by get_posts");
        $this->assertEquals($comics[0], $last, "get_boundary_post should match the last element returned by get_posts");

        $this->assertEquals($comics[$comic_index], $post);
        $this->assertEquals($comics[$comic_index - 1], $prev, "get_adjacent_post should match the element previous to the current element");
        $this->assertEquals($comics[$comic_index + 1], $next, "get_adjacent_post should match the element after the current element");
    }
}