<?php

namespace Tests\Unit;

use App\Models\ForumPost;
use Tests\TestCase;

class ForumPostImageTest extends TestCase
{
    public function test_database_backed_attachment_is_preferred_over_a_missing_disk_file(): void
    {
        $inlineImage = 'data:image/jpeg;base64,' . base64_encode('forum-image');
        $post = new ForumPost([
            'post_image' => 'missing-image.jpg',
            'post_image_data' => $inlineImage,
        ]);

        $this->assertSame($inlineImage, $post->post_image_url);
    }

    public function test_invalid_inline_attachment_falls_back_to_the_file_lookup(): void
    {
        $post = new ForumPost([
            'post_image' => null,
            'post_image_data' => 'not-an-image',
        ]);

        $this->assertNull($post->post_image_url);
    }
}
