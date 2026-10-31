<?php

namespace Database\Seeders;

use App\Models\ForumComment;
use App\Models\ForumLike;
use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the legacy "[TEST]" forum fixtures with a realistic community feed
 * authored by the seeded health workers and mothers.
 *
 * Idempotent: posts, comments and likes are matched by content/user pairs, so
 * repeated boots only clean up leftovers and never duplicate the feed.
 */
class CommunityForumSeeder extends Seeder
{
    private int $rotation = 0;

    public function run(): void
    {
        $this->removeTestPosts();

        $women = $this->womenPool();
        $bhw = $this->findUser('ana@gmail.com') ?? $this->staffByRole('bhw');
        $midwife = $this->findUser('midwife@reprocare.com') ?? $this->staffByRole('midwife');

        if ($women->isEmpty() && ! $bhw && ! $midwife) {
            $this->command?->warn('CommunityForumSeeder: no users available to author posts.');

            return;
        }

        $now = now();

        foreach ($this->posts() as $definition) {
            $author = $this->resolveAuthor($definition['author'], $women, $bhw, $midwife);
            if (! $author) {
                continue;
            }

            $createdAt = $now->copy()->subHours($definition['hours_ago']);

            $post = ForumPost::firstOrCreate(
                ['content' => $definition['content']],
                ['user_id' => $author->id, 'status' => 'active']
            );

            if ($post->wasRecentlyCreated) {
                $post->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->saveQuietly();
            }

            foreach ($definition['comments'] ?? [] as $index => $comment) {
                $commenter = $this->resolveAuthor(
                    $comment['author'], $women, $bhw, $midwife, [$author->id]
                );
                if (! $commenter) {
                    continue;
                }

                $created = ForumComment::firstOrCreate(
                    [
                        'post_id' => $post->id,
                        'user_id' => $commenter->id,
                        'content' => $comment['content'],
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $stamp = $createdAt->copy()->addHours($comment['after_hours'] ?? $index + 1);
                    if ($stamp->greaterThan($now)) {
                        $stamp = $now->copy();
                    }
                    $created->forceFill(['created_at' => $stamp, 'updated_at' => $stamp])->saveQuietly();
                }
            }

            foreach ($definition['likes'] ?? [] as $email) {
                $liker = $this->resolveAuthor($email, $women, $bhw, $midwife, [$author->id]);
                if ($liker) {
                    $this->addLike($post, $liker);
                }
            }
        }

        $this->command?->info('Community forum: test posts removed and curated feed ready.');
    }

    private function removeTestPosts(): void
    {
        ForumPost::where('content', 'like', '%[TEST]%')
            ->get()
            ->each(function (ForumPost $post) {
                $post->comments()->delete();
                $post->likes()->delete();
                $post->delete();
            });
    }

    private function womenPool(): Collection
    {
        $women = User::where('role', 'user')->get();

        $named = $women->reject(fn (User $user) => str_starts_with($user->email, 'reprocare-test-'));

        return $named->isNotEmpty() ? $named->values() : $women->values();
    }

    private function staffByRole(string $role): ?User
    {
        return User::where('role', $role)->orderBy('id')->first();
    }

    private function findUser(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    private function resolveAuthor(
        string $preferred,
        Collection $women,
        ?User $bhw,
        ?User $midwife,
        array $excludeIds = []
    ): ?User {
        if ($preferred === 'bhw' && $bhw && ! in_array($bhw->id, $excludeIds, true)) {
            return $bhw;
        }
        if ($preferred === 'midwife' && $midwife && ! in_array($midwife->id, $excludeIds, true)) {
            return $midwife;
        }

        $user = $this->findUser($preferred);
        if ($user && ! in_array($user->id, $excludeIds, true)) {
            return $user;
        }

        $candidates = $women
            ->reject(fn (User $woman) => in_array($woman->id, $excludeIds, true))
            ->values();

        if ($candidates->isEmpty()) {
            $candidates = $women;
        }
        if ($candidates->isEmpty()) {
            return $bhw ?? $midwife;
        }

        $picked = $candidates[$this->rotation % $candidates->count()];
        $this->rotation++;

        return $picked;
    }

    private function addLike(ForumPost $post, User $user): void
    {
        if ($user->id === $post->user_id) {
            return;
        }

        if (ForumLike::where('post_id', $post->id)->where('user_id', $user->id)->exists()) {
            return;
        }

        $like = new ForumLike(['post_id' => $post->id, 'user_id' => $user->id]);
        if (Schema::hasColumn('forum_likes', 'user_id_old__deprecated')) {
            $like->setAttribute('user_id_old__deprecated', $user->id);
        }
        $like->save();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function posts(): array
    {
        return [
            [
                'author' => 'madonna@gmail.com',
                'hours_ago' => 4,
                'content' => "Finally had my prenatal checkup at RHU 1 today. The midwife was very patient and explained my lab results. For first-time moms like me, bring your PhilHealth ID and your previous records if you have them. Sharing in case it helps!",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Thank you for sharing, Madonna! You can also ask your BHW for your scheduled checkup dates so you do not miss one.', 'after_hours' => 1],
                    ['author' => 'midwife', 'content' => 'Glad your visit went well. Keep taking your prenatal vitamins and follow your next appointment schedule.', 'after_hours' => 2],
                ],
                'likes' => ['mariasanta@gmail.com', 'maria.clara@example.test', 'elena.santos.highrisk@example.test', 'rosa.flores@reprocare.test'],
            ],
            [
                'author' => 'mariasanta@gmail.com',
                'hours_ago' => 18,
                'content' => "How do you all keep track of your cycle? I used to write the dates on a calendar, but I keep losing it. Any simple tips?",
                'comments' => [
                    ['author' => 'maria.clara@example.test', 'content' => 'I use the cycle tracker here in ReproCare. It shows my next expected period and I can show it to my midwife during checkups.', 'after_hours' => 2],
                    ['author' => 'elena.santos.highrisk@example.test', 'content' => 'Same, the app helped me notice my cycle was irregular. My midwife asked me to keep logging for three months.', 'after_hours' => 5],
                ],
                'likes' => ['madonna@gmail.com', 'lorna.santos@reprocare.test', 'carmen.reyes@reprocare.test'],
            ],
            [
                'author' => 'rosa.flores@reprocare.test',
                'hours_ago' => 27,
                'content' => "Severe menstrual cramps during my period. What helps you? I already use a hot compress, but the first two days are still hard.",
                'comments' => [
                    ['author' => 'bhw', 'content' => 'Hot compress and light walking help, but if the pain makes it hard to work or you bleed heavily, please visit the RHU so the midwife can check you.', 'after_hours' => 3],
                    ['author' => 'gloria.aquino@reprocare.test', 'content' => 'Warm ginger tea and stretching helped me a lot. Rest when you can.', 'after_hours' => 6],
                ],
                'likes' => ['fe.mendoza@reprocare.test', 'helen.bautista@reprocare.test', 'irene.tolentino@reprocare.test', 'julia.navarro@reprocare.test', 'delia.cruz@reprocare.test'],
            ],
            [
                'author' => 'fe.mendoza@reprocare.test',
                'hours_ago' => 44,
                'content' => "Morning sickness tips? I am 9 weeks pregnant and I feel nauseous in the morning and again before dinner.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Try small frequent meals, avoid strong smells, and sip water throughout the day. If you cannot keep food or water down, please come to the RHU.', 'after_hours' => 2],
                    ['author' => 'julia.navarro@reprocare.test', 'content' => 'Crackers before getting out of bed helped me. Sour fruits also helped a little!', 'after_hours' => 7],
                ],
                'likes' => ['madonna@gmail.com', 'maria.clara@example.test', 'carmen.reyes@reprocare.test'],
            ],
            [
                'author' => 'gloria.aquino@reprocare.test',
                'hours_ago' => 68,
                'content' => "I switched from the injectable to pills after talking to my midwife. Is it normal to feel a little nauseous in the first week?",
                'comments' => [
                    ['author' => 'bhw', 'content' => 'It can happen while your body adjusts. Please tell your midwife at your next visit so she can note it in your record.', 'after_hours' => 3],
                    ['author' => 'helen.bautista@reprocare.test', 'content' => 'I had the same experience and it went away after a week or two. Still best to ask your midwife.', 'after_hours' => 9],
                ],
                'likes' => ['rosa.flores@reprocare.test', 'delia.cruz@reprocare.test'],
            ],
            [
                'author' => 'maria.clara@example.test',
                'hours_ago' => 90,
                'content' => "First trimester fatigue is real. I sleep early but still feel tired at work. How do you manage?",
                'comments' => [
                    ['author' => 'delia.cruz@reprocare.test', 'content' => 'I took short breaks and asked my family for help with chores. It got better for me in the second trimester.', 'after_hours' => 4],
                    ['author' => 'midwife', 'content' => 'Rest when possible and keep your checkups. If the tiredness is severe or you feel dizzy, have yourself checked.', 'after_hours' => 10],
                ],
                'likes' => ['mariasanta@gmail.com', 'elena.santos.highrisk@example.test', 'fe.mendoza@reprocare.test', 'julia.navarro@reprocare.test'],
            ],
            [
                'author' => 'elena.santos.highrisk@example.test',
                'hours_ago' => 115,
                'content' => "Sharing because it might help other first-time moms: my midwife taught me the danger signs to watch for, like heavy bleeding, severe headache, blurred vision, and baby moving less. Please do not wait if you feel any of these.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Very important reminder, Elena. Save the RHU hotline in your phone and tell your family about these signs too.', 'after_hours' => 2],
                    ['author' => 'mariasanta@gmail.com', 'content' => 'Thank you, I did not know about the baby movement part until now.', 'after_hours' => 5],
                ],
                'likes' => ['madonna@gmail.com', 'maria.clara@example.test', 'rosa.flores@reprocare.test', 'lorna.santos@reprocare.test', 'carmen.reyes@reprocare.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'ana@gmail.com',
                'hours_ago' => 140,
                'content' => "Reminder for our mothers in Barangay Cacaritan: free prenatal checkups and vitamins are available at RHU 1 every weekday. Message your BHW if you need help with your schedule.",
                'comments' => [
                    ['author' => 'mariasanta@gmail.com', 'content' => 'Thank you Maam Ana! I will visit this week.', 'after_hours' => 3],
                    ['author' => 'madonna@gmail.com', 'content' => 'This is very helpful, thank you po.', 'after_hours' => 6],
                ],
                'likes' => ['maria.clara@example.test', 'elena.santos.highrisk@example.test', 'rosa.flores@reprocare.test', 'fe.mendoza@reprocare.test', 'gloria.aquino@reprocare.test', 'helen.bautista@reprocare.test', 'julia.navarro@reprocare.test'],
            ],
            [
                'author' => 'lorna.santos@reprocare.test',
                'hours_ago' => 170,
                'content' => "Breastfeeding question: my baby is 3 weeks old and I sometimes feel a sharp pain during feeding. Is that normal?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'A little tenderness can happen at the start, but sharp pain may mean the latch needs adjusting. You can visit the RHU breastfeeding station so we can help you position the baby.', 'after_hours' => 5],
                    ['author' => 'fe.mendoza@reprocare.test', 'content' => 'It helped me to feed skin to skin and ask the midwife to check my latch.', 'after_hours' => 11],
                ],
                'likes' => ['carmen.reyes@reprocare.test', 'delia.cruz@reprocare.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'carmen.reyes@reprocare.test',
                'hours_ago' => 200,
                'content' => "Postpartum recovery question: how long did your bleeding last? I am 2 weeks after giving birth and still wearing a pad.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'It varies, but the bleeding should gradually become lighter. If it becomes heavy again, has a bad smell, or you feel dizzy, come to the RHU right away.', 'after_hours' => 4],
                    ['author' => 'lorna.santos@reprocare.test', 'content' => 'Mine lightened around 3 to 4 weeks. Rest and continue your iron.', 'after_hours' => 9],
                ],
                'likes' => ['ana@gmail.com', 'helen.bautista@reprocare.test'],
            ],
            [
                'author' => 'julia.navarro@reprocare.test',
                'hours_ago' => 240,
                'content' => "Any budget-friendly iron-rich foods you can recommend? My midwife said my hemoglobin is a little low.",
                'comments' => [
                    ['author' => 'carmen.reyes@reprocare.test', 'content' => 'Malunggay, monggo, malunggay leaves with egg, and kamote helped me. I also take my iron tablet with vitamin C.', 'after_hours' => 6],
                    ['author' => 'ana@gmail.com', 'content' => 'Please take your iron at the same time each day and avoid coffee or tea right after.', 'after_hours' => 13],
                ],
                'likes' => ['elena.garcia@reprocare.test', 'delia.cruz@reprocare.test', 'fe.mendoza@reprocare.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'helen.bautista@reprocare.test',
                'hours_ago' => 290,
                'content' => "I feel anxious before every checkup. Is it okay to bring my husband with me for support?",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Yes, you can bring a companion. The RHU midwives are there to answer your questions, so do not be shy.', 'after_hours' => 3],
                    ['author' => 'irene.tolentino@reprocare.test', 'content' => 'I always bring my sister. It helps me remember what the midwife says.', 'after_hours' => 8],
                ],
                'likes' => ['maria.clara@example.test', 'julia.navarro@reprocare.test', 'madonna@gmail.com'],
            ],
            [
                'author' => 'irene.tolentino@reprocare.test',
                'hours_ago' => 340,
                'content' => "How do you stay hydrated when you are always busy? I forget to drink water until I already feel dizzy.",
                'comments' => [
                    ['author' => 'delia.cruz@reprocare.test', 'content' => 'I fill a bottle and keep it beside me, then drink a glass with every meal.', 'after_hours' => 5],
                    ['author' => 'midwife', 'content' => 'Dizziness can be a sign of dehydration or low blood pressure. Please rest and get checked if it happens often.', 'after_hours' => 12],
                ],
                'likes' => ['helen.bautista@reprocare.test', 'rosa.flores@reprocare.test'],
            ],
            [
                'author' => 'delia.cruz@reprocare.test',
                'hours_ago' => 400,
                'content' => "Went to RHU 1 for my postpartum checkup and my baby got her first vaccines. The staff explained each one. To new moms: do not be afraid to ask questions.",
                'comments' => [
                    ['author' => 'maria.clara@example.test', 'content' => 'Thank you for sharing! I was nervous about the vaccine schedule.', 'after_hours' => 4],
                    ['author' => 'ana@gmail.com', 'content' => 'Congratulations, Delia! Keep the baby record book safe for the next visits.', 'after_hours' => 7],
                ],
                'likes' => ['madonna@gmail.com', 'mariasanta@gmail.com', 'carmen.reyes@reprocare.test', 'lorna.santos@reprocare.test', 'julia.navarro@reprocare.test'],
            ],
            [
                'author' => 'madonna@gmail.com',
                'hours_ago' => 470,
                'content' => "Tips for sleeping during pregnancy? I am in my third trimester and it is hard to get comfortable at night.",
                'comments' => [
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Sleeping on my side with a pillow between my knees helped me a lot.', 'after_hours' => 6],
                    ['author' => 'midwife', 'content' => 'Try a pillow under your back and keep the room cool. If you feel short of breath, tell your midwife.', 'after_hours' => 14],
                ],
                'likes' => ['maria.clara@example.test', 'fe.mendoza@reprocare.test', 'helen.bautista@reprocare.test'],
            ],
            [
                'author' => 'mariasanta@gmail.com',
                'hours_ago' => 550,
                'content' => "Any other mothers here with irregular periods? Mine changes every month. I log it, but I am not sure when to worry.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Irregular cycles can have many causes. Keep logging for a few months and let the midwife review your records. If you have heavy bleeding or pain, visit sooner.', 'after_hours' => 5],
                    ['author' => 'elena.santos.highrisk@example.test', 'content' => 'Logging really helped my midwife see the pattern. Hang in there.', 'after_hours' => 11],
                ],
                'likes' => ['madonna@gmail.com', 'rosa.flores@reprocare.test', 'irene.tolentino@reprocare.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'elena.garcia@reprocare.test',
                'hours_ago' => 640,
                'content' => "Family planning advice? I am thinking about the injectable, but I want to understand the side effects first.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'The midwife can explain all the options and help you choose what fits you. Just visit RHU 1 and we will guide you.', 'after_hours' => 7],
                    ['author' => 'gloria.aquino@reprocare.test', 'content' => 'I was on the injectable for a year. I had spotting at first, but my midwife explained it to me.', 'after_hours' => 15],
                ],
                'likes' => ['fe.mendoza@reprocare.test', 'julia.navarro@reprocare.test', 'carmen.reyes@reprocare.test'],
            ],
            [
                'author' => 'maria.clara@example.test',
                'hours_ago' => 740,
                'content' => "Thank you to the BHWs and midwives who checked on us after the typhoon. Our family really appreciated the follow-up messages.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Thank you for your kind words! Message us anytime you need help.', 'after_hours' => 3],
                    ['author' => 'madonna@gmail.com', 'content' => 'Yes, thank you so much po.', 'after_hours' => 9],
                ],
                'likes' => ['mariasanta@gmail.com', 'elena.santos.highrisk@example.test', 'rosa.flores@reprocare.test', 'lorna.santos@reprocare.test', 'delia.cruz@reprocare.test', 'helen.bautista@reprocare.test', 'irene.tolentino@reprocare.test', 'julia.navarro@reprocare.test'],
            ],
            [
                'author' => 'fe.mendoza@reprocare.test',
                'hours_ago' => 850,
                'content' => "Can I continue light exercise during pregnancy? I walk around our barangay every morning.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Light walking is usually fine. Avoid overheating and drink water. If you feel pain, bleeding, or contractions, stop and rest, then visit the RHU.', 'after_hours' => 4],
                    ['author' => 'julia.navarro@reprocare.test', 'content' => 'My midwife told me walking helped with my lower back pain.', 'after_hours' => 10],
                ],
                'likes' => ['madonna@gmail.com', 'mariasanta@gmail.com', 'elena.garcia@reprocare.test', 'carmen.reyes@reprocare.test'],
            ],
            [
                'author' => 'gloria.aquino@reprocare.test',
                'hours_ago' => 960,
                'content' => "Reminder to schedule your checkup before the long weekend. I almost forgot mine and the BHW kindly reminded me.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Thank you for the reminder! We send messages to our mothers every week.', 'after_hours' => 3],
                    ['author' => 'lorna.santos@reprocare.test', 'content' => 'Just scheduled mine for Monday.', 'after_hours' => 8],
                ],
                'likes' => ['maria.clara@example.test', 'irene.tolentino@reprocare.test'],
            ],
        ];
    }
}
