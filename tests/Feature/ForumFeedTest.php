<?php

namespace Tests\Feature;

use App\Models\ForumLike;
use App\Models\ForumPost;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ForumFeedTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('forum_posts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('content')->nullable();
            $t->string('post_image')->nullable();
            $t->longText('post_image_data')->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('forum_likes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('post_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamps();
        });

        Schema::create('forum_comments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('post_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('content')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function test_the_index_trades_the_pager_for_a_scroll_sentinel(): void
    {
        $this->seedPosts(4);

        $html = $this->actingAs($this->patient())->get(route('forum.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="forumFeedSentinel"', $html);
        $this->assertStringNotContainsString('page=2', $html);
        // Too few posts to fill a page, so the tail must not promise more.
        $this->assertStringContainsString('data-has-more="0"', $html);
    }

    public function test_the_scroll_hands_the_reader_fields_to_resume_from(): void
    {
        $this->seedPosts(12);
        $viewer = $this->patient();

        $html = $this->actingAs($viewer)->get(route('forum.index'))
            ->assertOk()
            ->getContent();

        // The feed collapses what was already read by timestamp, so a card
        // without one cannot be placed and gets shown again next visit.
        preg_match_all('/data-post-id="(\d+)"\s+data-created="(\d+)"/', $html, $cards);
        $this->assertCount(10, $cards[1], 'every rendered card needs a resume timestamp');
        $this->assertMatchesRegularExpression('/data-user="' . $viewer->id . '"/', $html);

        // Appended cards stay invisible unless the layout is told to observe them.
        $this->assertStringContainsString('window.ReproCareReveal(feed)', $html);
    }

    public function test_scroll_pages_never_repeat_or_skip_a_post(): void
    {
        $this->seedPosts(23);
        $viewer = $this->patient();
        $this->actingAs($viewer);

        $first = $this->get(route('forum.index'))->assertOk()->getContent();
        $seen = $this->idsOf($first);
        $this->assertCount(10, $seen);
        $cursor = $this->cursorOf($first);

        // Posted while the reader is mid-scroll: it must not be stitched into
        // the stream they are already working through, which is what would
        // shift every later page and hand back a post they had read.
        $late = ForumPost::create(['user_id' => $viewer->id, 'content' => 'Landed mid-scroll.', 'status' => 'active']);

        for ($page = 2; $cursor !== '' && $page <= 10; $page++) {
            $decoded = json_decode(
                $this->get(route('forum.feed', ['cursor' => $cursor]))->assertOk()->getContent(),
                true
            );
            $next = $this->idsOf($decoded['html']);

            $this->assertSame([], array_values(array_intersect($seen, $next)), 'a post was served twice');

            $seen = array_merge($seen, $next);
            $cursor = ($decoded['hasMore'] ?? false) && !empty($decoded['cursor']) ? $decoded['cursor'] : '';
        }

        $this->assertSame('', $cursor, 'the feed kept promising pages past the walk guard');

        $expected = ForumPost::active()->whereKeyNot($late->id)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->pluck('id')->map(fn ($id) => (int) $id)->all();

        // Newest first, every post exactly once, nothing re-served after the
        // mid-scroll post arrived.
        $this->assertSame($expected, $seen, 'the scroll skipped, duplicated or reordered a post');
    }

    public function test_the_feed_is_one_full_width_column(): void
    {
        $this->seedPosts(3);

        $html = $this->actingAs($this->patient())->get(route('forum.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="forum-feed-column"', $html);
        $this->assertStringNotContainsString('col-lg-8', $html);
        $this->assertStringNotContainsString('col-lg-4', $html);
        $this->assertStringNotContainsString('Recent Discussions', $html);
    }

    public function test_the_composer_expands_on_a_draft_rather_than_on_focus(): void
    {
        // The OS file picker steals focus, so a :focus-within rule collapsed the
        // bar with the photo already chosen and "Add Photo" looked broken.
        $html = $this->actingAs($this->patient())->get(route('forum.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('.quick-composer-card.is-open .quick-inline-bar', $html);
        $this->assertStringNotContainsString(':focus-within .quick-inline-bar', $html);
    }

    public function test_a_photo_posted_from_the_composer_reaches_the_card(): void
    {
        Storage::fake('public');
        $viewer = $this->patient();

        // Hand-built PNG bytes: UploadedFile::fake()->image() needs the GD
        // extension, which this PHP build does not load.
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        $this->actingAs($viewer)->post(route('forum.store'), [
            'content' => 'An ultrasound attached from the inline composer.',
            'post_image' => UploadedFile::fake()->createWithContent('ultrasound.png', $png),
        ])->assertRedirect(route('forum.index'));

        $post = ForumPost::latest('id')->firstOrFail();
        $this->assertNotNull($post->post_image);
        Storage::disk('public')->assertExists('uploads/forum/' . $post->post_image);

        $html = $this->actingAs($viewer)->get(route('forum.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('post-media-frame', $html);
    }

    public function test_the_post_page_builds_its_title_from_escaped_text(): void
    {
        // A @section argument is plain PHP, so the '{{ … }}' this page used to
        // carry was never compiled and printed its own source into the browser
        // tab. startSection() escapes the finished value, so post text lands in
        // <title> as entities rather than as live markup.
        $author = $this->patient();
        $post = ForumPost::create([
            'user_id' => $author->id,
            'content' => '<script>alert(1)</script> prenatal vitamins',
        ]);

        $html = $this->actingAs($author)->get(route('forum.show', $post->id))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match('#<title>(.*?)</title>#s', $html, $title), 'no title element was rendered');
        // Counts rather than strings: the test printer re-escapes its own output,
        // so only these settle whether the title is escaped once or twice.
        $this->assertSame(0, substr_count($title[1], '<?'), 'the title still carries literal PHP source');
        $this->assertSame(0, substr_count($title[1], '<script>alert'), 'the title echoes raw post text');
        $this->assertSame(1, substr_count($title[1], '&lt;script&gt;'), 'post text is not escaped into the title');
        $this->assertSame(0, substr_count($title[1], '&amp;lt;'), 'post text is escaped twice');
        $this->assertStringContainsString(' - ReproCare Forum', $title[1]);
    }

    public function test_a_compressed_upload_keeps_its_inline_copy(): void
    {
        Storage::fake('public');
        $viewer = $this->patient();

        // forum-photo.js caps a photo at roughly this size before posting, and
        // the inline copy is what the feed renders from, because a deployment
        // without the disk file (or without PHP GD) would otherwise show
        // nothing. Padded PNG keeps the payload realistic without needing GD
        // to fabricate an image of it.
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $png .= str_repeat('z', 380000);

        $this->actingAs($viewer)->post(route('forum.store'), [
            'content' => 'A photo the browser already shrank.',
            'post_image' => UploadedFile::fake()->createWithContent('photo.jpg', $png),
        ])->assertSessionHasNoErrors();

        $post = ForumPost::latest('id')->firstOrFail();
        $this->assertStringStartsWith('data:image/', (string) $post->post_image_url);
    }

    public function test_a_photo_the_server_rejects_leaves_no_post_behind(): void
    {
        Storage::fake('public');

        $this->actingAs($this->patient())->post(route('forum.store'), [
            'content' => 'A file that is not a photo.',
            'post_image' => UploadedFile::fake()->create('notes.pdf', 100),
        ])->assertSessionHasErrors('post_image');

        $this->assertSame(0, ForumPost::count());
    }

    public function test_like_reports_the_state_that_is_actually_stored(): void
    {
        $post = $this->seedPosts(1)[0];
        $viewer = $this->patient();

        $this->actingAs($viewer)->postJson(route('forum.like', $post->id))
            ->assertOk()
            ->assertJson(['liked' => true, 'likes_count' => 1]);
        $this->assertSame(1, ForumLike::where('post_id', $post->id)->where('user_id', $viewer->id)->count());

        $this->actingAs($viewer)->postJson(route('forum.like', $post->id))
            ->assertOk()
            ->assertJson(['liked' => false, 'likes_count' => 0]);
        $this->assertSame(0, ForumLike::where('post_id', $post->id)->count());
    }

    private function idsOf(string $markup): array
    {
        preg_match_all('/class="forum-post-card[^"]*" id="post-(\d+)"/', $markup, $matches);

        return array_map('intval', $matches[1]);
    }

    private function cursorOf(string $html): string
    {
        preg_match('/data-cursor="([^"]*)"/', $html, $cursor);

        return $cursor[1] ?? '';
    }

    /**
     * @return array<int, ForumPost>
     */
    private function seedPosts(int $count): array
    {
        $author = $this->patient();

        return collect(range(1, $count))
            ->map(fn () => ForumPost::create([
                'user_id' => $author->id,
                'content' => 'Community discussion body.',
                'status' => 'active',
            ]))
            ->all();
    }
}
