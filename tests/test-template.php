<?php

class TemplateTest extends WP_UnitTestCase
{
    public function testIsComic()
    {
        $post_id = $this->factory()->post->create(array(
            'post_type' => MangaPress_Posts::POST_TYPE,
            'post_title' => 'Test Comic',
        ));

        $this->assertEquals(mangapress_is_comic($post_id), true);
    }
}