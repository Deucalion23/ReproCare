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
                'author' => 'helen.bautista@reprocare.test',
                'hours_ago' => 1,
                'content' => "Sikat ang init ngayon. Nagaantok ako kahit gabi naman. Pregnant ako sa second trimester — normal lang ba ito o kulang sa iron?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Panghihina at pagaka-antok ay madalas dahil sa anemia. Kumain ng karne, isdang malapit, liver, at ispinaca. Pwede kang pumunta sa RHU para sa free blood test.', 'after_hours' => 1],
                    ['author' => 'rosa.flores@reprocare.test', 'content' => 'Nakakatulong sa akin ang prenatal vitamins na binibigay sa RHU. Uminom ka kahit wala kang gana.', 'after_hours' => 1],
                ],
                'likes' => ['maria.clara@example.test', 'fe.mendoza@reprocare.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'julia.navarro@reprocare.test',
                'hours_ago' => 2,
                'content' => "Ang ganda ng experience ko sa normal delivery dito sa RHU 1. Supportive ang midwife, hintay niya ang contractions ko at pinagbigyan akong magwalk. Salamat po!",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Congrats, Julia! Ang baga nyo po? Ipakita sa BHW para ma-log sa child record.', 'after_hours' => 1],
                    ['author' => 'delia.cruz@reprocare.test', 'content' => 'Nakakatakot pero nakaka-good news naman basahin ito. Salamat sa sharing.', 'after_hours' => 2],
                ],
                'likes' => ['madonna@gmail.com', 'mariasanta@gmail.com', 'elena.garcia@reprocare.test', 'carmen.reyes@reprocare.test', 'lorna.santos@reprocare.test'],
            ],
            [
                'author' => 'irene.tolentino@reprocare.test',
                'hours_ago' => 3,
                'content' => "Question: pwede pa bang magpabakuna against rabies kahit buntis ako? Kagat ako ng aso kagabi sa likod ng bahay namin.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Pumunta agad sa RHU o animal bite center. Ang anti-rabies vaccine ay kailangan kahit buntis — mas delikado ang rabies. Huwag nang magtago ng kagat.', 'after_hours' => 1],
                    ['author' => 'helen.bautista@reprocare.test', 'content' => 'Ako rin nabatukan ng aso dati, tinanggal ko agad ang sabon at tumakbo sa health center.', 'after_hours' => 2],
                ],
                'likes' => ['maria.clara@example.test', 'gloria.aquino@reprocare.test', 'rosa.flores@reprocare.test'],
            ],
            [
                'author' => 'carmen.reyes@reprocare.test',
                'hours_ago' => 6,
                'content' => "Nagpa-Pap smear ako kahit sa RHU at libre lang. 45 minutes lang ang hintay. Para sa mga nanay na takot: mas mabilis pa sa checkup ng dugo.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Salamat Carmen. Ang cervical cancer screening ay libre para sa mga women sa ating barangay. Once a year para sa may history, every three years kung normal.', 'after_hours' => 2],
                ],
                'likes' => ['elena.garcia@reprocare.test', 'fe.mendoza@reprocare.test', 'lorna.santos@reprocare.test', 'delia.cruz@reprocare.test'],
            ],
            [
                'author' => 'lorna.santos@reprocare.test',
                'hours_ago' => 9,
                'content' => "Ano po ang mas okay, implant o IUD? Two years na akong nagpapatingin sa dalawa ko at gusto na naming magpahinga muna.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Pareho pong available sa RHU at libre. Ang implant tatagal ng three years, ang IUD hanggang eight. Dumayo lang para ipaliwanag ng midwife kung alin ang angay sa inyo.', 'after_hours' => 3],
                    ['author' => 'mariasanta@gmail.com', 'content' => 'Nag-implant ako, wala akong side effect after ng unang tatlong buwan.', 'after_hours' => 5],
                ],
                'likes' => ['irene.tolentino@reprocare.test', 'julia.navarro@reprocare.test', 'carmen.reyes@reprocare.test'],
            ],
            [
                'author' => 'gloria.aquino@reprocare.test',
                'hours_ago' => 12,
                'content' => "Paalala sa mga kapitbahay: may feeding program tuwing Martes at Huwebes sa covered court. Dalhin ang bata at ang child health record.",
                'comments' => [
                    ['author' => 'delia.cruz@reprocare.test', 'content' => 'Salamat sa reminder, Ate Gloria! Nakakatulong ang vitamins na binibigay doon.', 'after_hours' => 1],
                    ['author' => 'ana@gmail.com', 'content' => 'Ang mga buntis ay pwede ring sumali para sa free snack at iron tablets.', 'after_hours' => 2],
                ],
                'likes' => ['helen.bautista@reprocare.test', 'rosa.flores@reprocare.test', 'maria.clara@example.test'],
            ],
            [
                'author' => 'fe.mendoza@reprocare.test',
                'hours_ago' => 21,
                'content' => "Nawala yung regla ko ng dalawang buwan. Positive ang home test. Ayoko pang malaman ng magulang ko. Saan ako pwedeng pumunta na ligtas?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Pumunta ka sa RHU, kausapin mo ako nang mag-isa kung gusto mo. Titingnan natin ang pregnancy mo at ipapaliwanag natin ang lahat ng choice mo. Walang husga dito.', 'after_hours' => 1],
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Kakampi mo kami dito. Huwag kang magpadala sa bibingka na gamot, delikado.', 'after_hours' => 3],
                ],
                'likes' => ['irene.tolentino@reprocare.test', 'julia.navarro@reprocare.test', 'lorna.santos@reprocare.test', 'carmen.reyes@reprocare.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'elena.garcia@reprocare.test',
                'hours_ago' => 30,
                'content' => "Yung unang gatas na dilaw (colostrum) — huwag ninyong itatapon! Sabi ng midwife ko, iyon ang pinaka-importanteng bakuna ng bagong bati.",
                'comments' => [
                    ['author' => 'madonna@gmail.com', 'content' => 'Ako akala ko masama kasi dilaw. Natutunan ko lang dito.', 'after_hours' => 4],
                    ['author' => 'midwife', 'content' => 'Tama si Ate Elena. Ang colostrum ayaman sa antibody. Ipagpatuloy ang exclusive breastfeeding ng unanim na buwan.', 'after_hours' => 6],
                ],
                'likes' => ['delia.cruz@reprocare.test', 'rosa.flores@reprocare.test', 'helen.bautista@reprocare.test'],
            ],
            [
                'author' => 'rosa.flores@reprocare.test',
                'hours_ago' => 36,
                'content' => "Saan po merong libreng folic acid? Nalaman ko lang na dapat uminom bago pa magbuntis. Wala kaming budget sa botika.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Libre sa RHU 1, bukas ang Monday to Friday. Humingi lang ng folic acid sa counter at i-check ang name mo sa listahan.', 'after_hours' => 2],
                    ['author' => 'maria.clara@example.test', 'content' => 'May free vitamins din sa prenatal checkup, huwag kang mahihiyang humingi.', 'after_hours' => 4],
                ],
                'likes' => ['fe.mendoza@reprocare.test', 'lorna.santos@reprocare.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'delia.cruz@reprocare.test',
                'hours_ago' => 50,
                'content' => "Hindi ko ma-log ang period ko tuwing nagkakaroon ng stress sa trabaho. Normal lang ba na maglate ng isang buwan?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang stress, biglaang pagbaba ng timbang, at sobrang exercise ay nakaka-antala ng regla. Kung tatlong buwang walang regla at hindi ka buntis, magpatingin ka sa RHU.', 'after_hours' => 3],
                    ['author' => 'julia.navarro@reprocare.test', 'content' => 'Nang log ko ang late dates dito, nakita ko na pattern tuwing May.', 'after_hours' => 8],
                ],
                'likes' => ['irene.tolentino@reprocare.test', 'carmen.reyes@reprocare.test', 'elena.garcia@reprocare.test'],
            ],
            [
                'author' => 'maria.clara@example.test',
                'hours_ago' => 60,
                'content' => "Three days na hindi ako nakapag-ihi nang maayos after ng cesarea. Sumakit na ang tiyan ko. Delikado po ba ito?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Oo, ipakita agad ang sarili mo. Ang hirap pag-ihi pagkatapos ng operation ay maaaring impeksyon o problema sa bladder. Huwag nang hintayin ang kinabukasan.', 'after_hours' => 1],
                    ['author' => 'ana@gmail.com', 'content' => 'Pwede kitang samahan sa RHU bukas umaga, sabihin mo lang.', 'after_hours' => 2],
                ],
                'likes' => ['rosa.flores@reprocare.test', 'helen.bautista@reprocare.test'],
            ],
            [
                'author' => 'madonna@gmail.com',
                'hours_ago' => 76,
                'content' => "Nakakatulong ang text reminder ng RHU. Kahapon pa lang ako nagpa-appointment at may message na ako kung kailan. Pero sana may text din para sa vitamins.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Salamat sa feedback. Idadagdag natin ang reminder para sa iron at prenatal vitamins sa susunod na buwan.', 'after_hours' => 5],
                ],
                'likes' => ['mariasanta@gmail.com', 'delia.cruz@reprocare.test', 'lorna.santos@reprocare.test', 'fe.mendoza@reprocare.test'],
            ],
            [
                'author' => 'helen.bautista@reprocare.test',
                'hours_ago' => 96,
                'content' => "May lump sa aking dibdib. Nag-self exam ako mula sa video dito sa Learning Materials. Natatakot ako pero nag-book na ako sa RHU.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Maganda na ginawa mo. Karamihan sa mga lump ay hindi cancer, pero dapat tingnan. Dalhin ang record kung kailan mo ito naramdaman.', 'after_hours' => 2],
                    ['author' => 'carmen.reyes@reprocare.test', 'content' => 'Ako rin nagkaganyan, benign lang pala. Pero kailangan mong magpatingin talaga.', 'after_hours' => 6],
                ],
                'likes' => ['elena.garcia@reprocare.test', 'rosa.flores@reprocare.test', 'gloria.aquino@reprocare.test', 'julia.navarro@reprocare.test'],
            ],
            [
                'author' => 'gloria.aquino@reprocare.test',
                'hours_ago' => 110,
                'content' => "Para sa mga may menopous na parang akin — mainit bigla ang katawan, hindi makatulog, at ang sakit ng tuhod. May libreng checkup ba tayo rito kahit hindi na buntis?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Oo. Ang RHU ay para sa lahat ng health concern ng babae: screening, blood pressure, at payo tungkol sa sleep at bones. Huwag isipin na para lang sa buntis.', 'after_hours' => 3],
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Naglakad ako tuwing umaga, nakatulong sa tulog ko.', 'after_hours' => 9],
                ],
                'likes' => ['lorna.santos@reprocare.test', 'delia.cruz@reprocare.test', 'carmen.reyes@reprocare.test'],
            ],
            [
                'author' => 'lorna.santos@reprocare.test',
                'hours_ago' => 128,
                'content' => "Araw-araw akong umiinom ng kape, tatlong baso. Sabi ng isa, nakakasama sa buntis. Totoo po ba?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Hanggang isang tasa (200 mg caffeine) sa isang araw ang safe habang buntis. Ang sobra ay nakaka-antok sa gabi at nakaka-dagdag ng tibok ng puso.', 'after_hours' => 4],
                    ['author' => 'fe.mendoza@reprocare.test', 'content' => 'Pinalitan ko ng banana chip ang kape ko, okay lang naman ako.', 'after_hours' => 10],
                ],
                'likes' => ['helen.bautista@reprocare.test', 'maria.clara@example.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'julia.navarro@reprocare.test',
                'hours_ago' => 150,
                'content' => "Yung anak ko na three years, hindi na nagpa-checkup since ng six months. May multa ba kung late na sa vaccines?",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Walang multa! May catch-up schedule. Dalhin lang ang child record sa RHU tuwing Lunes to Biyernes, titingnan natin kung alin ang kulang.', 'after_hours' => 2],
                    ['author' => 'midwife', 'content' => 'Mahalaga ang catch-up. Ang bakuna na lumampas sa schedule ay pwede pa ring ipagpatuloy.', 'after_hours' => 5],
                ],
                'likes' => ['delia.cruz@reprocare.test', 'mariasanta@gmail.com', 'gloria.aquino@reprocare.test', 'carmen.reyes@reprocare.test'],
            ],
            [
                'author' => 'elena.santos.highrisk@example.test',
                'hours_ago' => 168,
                'content' => "High blood pressure ako sa unang pregnancy. Sa pangalawa, anu-ano ang dapat kong i-prepare para hindi na maulit?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Magpatingin nang maaga (first trimester), sukatin ang BP tuwing visit, at i-report agad ang sakit ng ulo, pananaw na nangingitim, at pamamaga ng kamay. Bantayan ang asin.', 'after_hours' => 3],
                    ['author' => 'rosa.flores@reprocare.test', 'content' => 'Ang BP monitor sa RHU ay libre gamitin, tuwing umaga.', 'after_hours' => 7],
                ],
                'likes' => ['maria.clara@example.test', 'helen.bautista@reprocare.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'carmen.reyes@reprocare.test',
                'hours_ago' => 190,
                'content' => "Nahihiya akong magtanong sa midwife tungkol sa sex during pregnancy. Pero kailangan ko lang malaman. Pwede ba?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Normal ang tanong na iyan at huwag mahiya. Sa uncomplicated pregnancy, ligtas ito. Iwasan kung may dugo, ang sakit, o sabi ng doctor na magpahinga.', 'after_hours' => 2],
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Ako rin nahihiya noon. Ang tagal ng hinihintay ko para malaman ko na simple lang.', 'after_hours' => 6],
                ],
                'likes' => ['lorna.santos@reprocare.test', 'fe.mendoza@reprocare.test', 'delia.cruz@reprocare.test'],
            ],
            [
                'author' => 'mariasanta@gmail.com',
                'hours_ago' => 210,
                'content' => "May UTI ako, ang sakit pag-ihi. Sabi ng katabi, uminom lang ng calamansi. Pumunta ako sa RHU at may libreng urine test pala.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang calamansi ay hindi gamot sa UTI. Kailangan ng tamang gamot na reseta. Sa buntis, ang hindi na-gamot na UTI ay nakaka-trigger ng preterm labor.', 'after_hours' => 4],
                    ['author' => 'ana@gmail.com', 'content' => 'Uminom ng maraming tubig at huwag pigilan ang pag-ihi.', 'after_hours' => 8],
                ],
                'likes' => ['irene.tolentino@reprocare.test', 'julia.navarro@reprocare.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'fe.mendoza@reprocare.test',
                'hours_ago' => 232,
                'content' => "Araw-araw akong pagod kahit wala akong ginagawa. Buntis ako ng 7 weeks. Akala ko ako na lang tamad.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang pagod sa unang trimester ay dahil sa hormones, hindi katamaran. Ang katawan ay bumubuo ng bagong buhay. Magpahinga kapag kaya, at ipa-check ang dugo para sa anemia.', 'after_hours' => 3],
                    ['author' => 'madonna@gmail.com', 'content' => 'Ganyan din ako sa una. Nang i-check ang dugo ko, mababa pala ang iron.', 'after_hours' => 9],
                ],
                'likes' => ['rosa.flores@reprocare.test', 'helen.bautista@reprocare.test', 'maria.clara@example.test', 'lorna.santos@reprocare.test'],
            ],
            [
                'author' => 'delia.cruz@reprocare.test',
                'hours_ago' => 260,
                'content' => "Nagpakonsulta ako sa family planning kahit may asawa. Hindi niya alam na pupunta ako. Supportive naman ang RHU, walang tanong-tanong.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang family planning ay karapatan mo. Kung gusto mong kausapin namin ang asawa mo nang kasama mo, andito kami — pero hindi niyo kailangan para makakuha ng kontrasepsyon.', 'after_hours' => 5],
                ],
                'likes' => ['carmen.reyes@reprocare.test', 'julia.navarro@reprocare.test', 'elena.garcia@reprocare.test'],
            ],
            [
                'author' => 'irene.tolentino@reprocare.test',
                'hours_ago' => 285,
                'content' => "Yung asawa ko, nagtatanong kung bakit ang bigla-bigla kong pagkagalit. Sabi ko, hormones lang. Nakatawa pero totoo.",
                'comments' => [
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Ipakita mo sa kanya ang Learning Materials tungkol sa mood changes. Ako ginawa ko yun, nakatulong.', 'after_hours' => 4],
                    ['author' => 'midwife', 'content' => 'Normal ang mood swings. Pero kung may iniisip na masama para sa sarili, sabihin agad sa amin.', 'after_hours' => 7],
                ],
                'likes' => ['fe.mendoza@reprocare.test', 'gloria.aquino@reprocare.test', 'helen.bautista@reprocare.test'],
            ],
            [
                'author' => 'rosa.flores@reprocare.test',
                'hours_ago' => 310,
                'content' => "Tag-init ngayon at mainit ang panahon. Paano po iiwasan ang heat stroke lalo na sa buntis na naglalakad sa bukid?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Maglakad tuwing maaga o hapon na. Dalhan ng tubig, magsinaw ng damit, at huminto kung maputla. Ang sobrang init ay nakaka-dehydrate at nakaka-contraction.', 'after_hours' => 2],
                    ['author' => 'ana@gmail.com', 'content' => 'May free oral rehydration sa RHU kung kinakailangan.', 'after_hours' => 6],
                ],
                'likes' => ['lorna.santos@reprocare.test', 'maria.clara@example.test', 'delia.cruz@reprocare.test'],
            ],
            [
                'author' => 'gloria.aquino@reprocare.test',
                'hours_ago' => 335,
                'content' => "Naghanda ako ng go bag: PhilHealth ID, prenatal record, damit ng bata, flashlight, at power bank. Nang dumating ang bagyo, handa na kami.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Ganito dapat. Ang mga buntis ay una naming inililipat kapag may bagyo. Sabihin sa BHW kung saan kayo lilipat.', 'after_hours' => 3],
                    ['author' => 'midwife', 'content' => 'I-screenshot din ang record sa telepono kung kaya, para kahabunot man ang bag.', 'after_hours' => 5],
                ],
                'likes' => ['irene.tolentino@reprocare.test', 'julia.navarro@reprocare.test', 'carmen.reyes@reprocare.test', 'helen.bautista@reprocare.test'],
            ],
            [
                'author' => 'helen.bautista@reprocare.test',
                'hours_ago' => 365,
                'content' => "Saan po mas mura ang ultrasound, sa RHU o sa hospital? Wala kaming budget ng sobra.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang referral namin ay sa public hospital, mas mura o libre para sa may PhilHealth. Magpakita muna sa RHU para makakuha ng referral slip.', 'after_hours' => 4],
                    ['author' => 'elena.santos.highrisk@example.test', 'content' => 'Dala ko ang PhilHealth ID ko at wala akong binayad sa ultrasound.', 'after_hours' => 8],
                ],
                'likes' => ['fe.mendoza@reprocare.test', 'rosa.flores@reprocare.test', 'maria.clara@example.test'],
            ],
            [
                'author' => 'lorna.santos@reprocare.test',
                'hours_ago' => 400,
                'content' => "Dalawang buwan na akong nalulungkot after ng delivery. Hindi ako marunong matulog kahit tulog na ang baby.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Salamat sa pag-amin nito. Ang postpartum depression ay tunay na sakit at may gamot. Bukas, kausapin mo ako nang mag-isa. Kung may iniisip kang masama para sa sarili, sabihin mo ngayon.', 'after_hours' => 1],
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Ako rin dumaan diyan. Nakatulong ang kwentuhan sa midwife. Hindi ka tamad, luya lang talaga ang pagka-ina.', 'after_hours' => 3],
                ],
                'likes' => ['delia.cruz@reprocare.test', 'julia.navarro@reprocare.test', 'carmen.reyes@reprocare.test', 'gloria.aquino@reprocare.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'maria.clara@example.test',
                'hours_ago' => 440,
                'content' => "First time kong nag-log ng cycle dito at nakita kong 34 days ang average ko. Akala ko broken ako kasi hindi 28 days. Salamat sa app!",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang 21 to 35 days ay normal. Ang mahalaga ay ang pattern mo, hindi ang numero ng iba.', 'after_hours' => 2],
                    ['author' => 'mariasanta@gmail.com', 'content' => 'Ganyan din ako, 33 days ako. Okay pala.', 'after_hours' => 6],
                ],
                'likes' => ['helen.bautista@reprocare.test', 'fe.mendoza@reprocare.test', 'rosa.flores@reprocare.test'],
            ],
            [
                'author' => 'julia.navarro@reprocare.test',
                'hours_ago' => 480,
                'content' => "May diabetes ba ang nagpapa-follow up dito? Nanay ko may hila ng asin at gusto naming matuto kung paano siya bantayan.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Pumunta kayo sa RHU para sa blood sugar check at payo sa pagkain. Ang gestational diabetes ay nangangailangan ng mas madalas na monitoring pero kaya namang kontrolin.', 'after_hours' => 5],
                    ['author' => 'ana@gmail.com', 'content' => 'May group namin tuwing Biyernes para sa blood pressure at asukal. Libre.', 'after_hours' => 9],
                ],
                'likes' => ['carmen.reyes@reprocare.test', 'lorna.santos@reprocare.test', 'elena.garcia@reprocare.test'],
            ],
            [
                'author' => 'elena.garcia@reprocare.test',
                'hours_ago' => 520,
                'content' => "Araw ng kalayaan para sa mga nanay: hindi ka masamang ina kung pinapayagan mong tulungan ka ng kapitbahay.",
                'comments' => [
                    ['author' => 'delia.cruz@reprocare.test', 'content' => 'Need ko pong marinig ito ngayon. Salamat Ate.', 'after_hours' => 2],
                    ['author' => 'midwife', 'content' => 'Ang community care ay bahagi ng pangangalaga. Tanggapin ang tulong.', 'after_hours' => 4],
                ],
                'likes' => ['gloria.aquino@reprocare.test', 'helen.bautista@reprocare.test', 'irene.tolentino@reprocare.test', 'rosa.flores@reprocare.test'],
            ],
            [
                'author' => 'carmen.reyes@reprocare.test',
                'hours_ago' => 570,
                'content' => "Yung anak ko na 13, may first period na. Hindi ko alam paano sasabihin. May guide ba dito sa community?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Sabiin mong normal ito at magandang palatandaan ng paglaki. Ipakita mo ang menstruation tracker sa app para matutunan niyang i-log ang sarili niya.', 'after_hours' => 3],
                    ['author' => 'mariasanta@gmail.com', 'content' => 'Binigyan ko siya ng maliit na notebook para dito niya isusulat. Nakatulong.', 'after_hours' => 7],
                ],
                'likes' => ['elena.garcia@reprocare.test', 'lorna.santos@reprocare.test', 'fe.mendoza@reprocare.test', 'julia.navarro@reprocare.test'],
            ],
            [
                'author' => 'irene.tolentino@reprocare.test',
                'hours_ago' => 620,
                'content' => "Saan po kayo pumupunta kapag gabi ang emergency? Wala kaming kotse dito sa dulo ng barangay.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Tawagan ang BHW ng purok ninyo. May listahan kami ng mga may motor na pwedeng tumulong sa gabi.', 'after_hours' => 2],
                    ['author' => 'midwife', 'content' => 'I-save ang number ng RHU sa telepono. Ang emergency hotline ay bukas 24 oras.', 'after_hours' => 5],
                ],
                'likes' => ['gloria.aquino@reprocare.test', 'helen.bautista@reprocare.test', 'maria.clara@example.test', 'rosa.flores@reprocare.test'],
            ],
            [
                'author' => 'delia.cruz@reprocare.test',
                'hours_ago' => 680,
                'content' => "Nagdudugo ako ng marami, may malalaking lumps pa. Tatlong araw na. Normal ba ito?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Hindi. Ang sobrang pagdurugo na kailangang palitan ang pad kada oras, o may malalaking lumps, ay dapat ipakita agad. Pumunta ka sa RHU ngayon.', 'after_hours' => 1],
                    ['author' => 'ana@gmail.com', 'content' => 'Samahan ko po ba kayo?', 'after_hours' => 1],
                ],
                'likes' => ['fe.mendoza@reprocare.test', 'carmen.reyes@reprocare.test', 'lorna.santos@reprocare.test'],
            ],
            [
                'author' => 'helen.bautista@reprocare.test',
                'hours_ago' => 740,
                'content' => "Nagpatingin ako sa anemia at binigyan ako ng iron tablets. Ang dumi ng ihi ko, itim! Natakot ako, pero sabi ng midwife normal lang pala.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Tama, ang iron ay nagpaitim ng dumi. Ang mahalaga: uminom araw-araw at kasama ang vitamin C, huwag kasama ang kape o gatas.', 'after_hours' => 3],
                    ['author' => 'maria.clara@example.test', 'content' => 'Ako rin nagulat noon. Salamat sa paalala sa vitamin C.', 'after_hours' => 6],
                ],
                'likes' => ['julia.navarro@reprocare.test', 'gloria.aquino@reprocare.test', 'elena.garcia@reprocare.test'],
            ],
            [
                'author' => 'mariasanta@gmail.com',
                'hours_ago' => 800,
                'content' => "Suggestion: magkaroon ng monthly meeting dito sa community para sa mga buntis sa parehong trimester. Mas madalas kaming magtanong kung magkakasama kami.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Magandang ideya. Itatakda namin sa RHU ang prenatal group session tuwing huling Sabado ng buwan.', 'after_hours' => 4],
                    ['author' => 'ana@gmail.com', 'content' => 'Ako ang magmememo sa mga purok.', 'after_hours' => 6],
                ],
                'likes' => ['delia.cruz@reprocare.test', 'irene.tolentino@reprocare.test', 'rosa.flores@reprocare.test', 'carmen.reyes@reprocare.test', 'lorna.santos@reprocare.test'],
            ],
            [
                'author' => 'gloria.aquino@reprocare.test',
                'hours_ago' => 880,
                'content' => "Nag-alala ako nang makita kong mababa ang blood pressure ko sa checkup. Sabi ng midwife, uminom lang ng maraming tubig at huwag biglaang tatayo.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Tama. Ang biglaang pagtayo ay nakakapagpapa-antok. Kung may panginginig o pagkahulog, ipaalam agad.', 'after_hours' => 5],
                ],
                'likes' => ['helen.bautista@reprocare.test', 'fe.mendoza@reprocare.test', 'elena.garcia@reprocare.test'],
            ],
            [
                'author' => 'fe.mendoza@reprocare.test',
                'hours_ago' => 960,
                'content' => "Hindi ako marunong mag-Android pero natutunan kong i-log ang period ko dito. Pindot lang at tapos na. Para sa mga katulad ko: kaya ninyo.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Tawag lang sa BHW kung gusto ninyong ituro. Masaya kaming tumulong.', 'after_hours' => 3],
                    ['author' => 'maria.clara@example.test', 'content' => 'Ganyan din ako natuto! Hindi na kailangan ng computer.', 'after_hours' => 8],
                ],
                'likes' => ['julia.navarro@reprocare.test', 'delia.cruz@reprocare.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'rosa.flores@reprocare.test',
                'hours_ago' => 1040,
                'content' => "May kaibigan akong nagpa-abortion sa labas. Natatakot ako para sa kanya. Saan siya pwedeng makakuha ng tulong na ligtas?",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Kung may dugo, lagnat, o sakit ng tiyan pagkatapos nito, dalhin agad sa hospital. Huwag siyang husgahan — kailangan niya ng pangangalaga. Andito kami pagkatapos para sa follow-up at family planning.', 'after_hours' => 2],
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Ang mahalaga, kasama mo siya.', 'after_hours' => 6],
                ],
                'likes' => ['carmen.reyes@reprocare.test', 'lorna.santos@reprocare.test', 'helen.bautista@reprocare.test', 'mariasanta@gmail.com'],
            ],
            [
                'author' => 'carmen.reyes@reprocare.test',
                'hours_ago' => 1140,
                'content' => "Isang taon na akong nagpaplano ng pangalawa. Yung app, nakakatulong talaga sa pag-track ng cycle para sa fertility awareness.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang fertility awareness ay nakakatulong para sa pagpaplano. Kung may tanong tungkol sa ovulation, magpakita lang sa RHU.', 'after_hours' => 4],
                ],
                'likes' => ['julia.navarro@reprocare.test', 'gloria.aquino@reprocare.test', 'delia.cruz@reprocare.test'],
            ],
            [
                'author' => 'lorna.santos@reprocare.test',
                'hours_ago' => 1250,
                'content' => "Salamat sa BHW na nag-follow up nang hindi ko nasagot ang text. Pumunta siya mismo sa bahay namin. Doon ko nalaman na delikado ang hindi pumunta sa checkup.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Basta kayo, uulitin ko pa. Ang importante ligtas kayo.', 'after_hours' => 2],
                    ['author' => 'midwife', 'content' => 'Ganyan ang serbisyo namin: text, tawag, at pagbisita. Salamat sa tiwala.', 'after_hours' => 5],
                ],
                'likes' => ['maria.clara@example.test', 'elena.garcia@reprocare.test', 'rosa.flores@reprocare.test', 'fe.mendoza@reprocare.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'elena.santos.highrisk@example.test',
                'hours_ago' => 1400,
                'content' => "Tip para sa mga high risk na katulad ko: i-keep ang lahat ng result sa isang folder. Nang maadmit ako, nakatulong ang buong history para mabilis ang desisyon.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Mahalaga ito. Ang complete record ay nagpapabilis ng tamang pangangalaga lalo na sa emergency.', 'after_hours' => 3],
                    ['author' => 'helen.bautista@reprocare.test', 'content' => 'Nag-scan na rin ako sa phone ko, pareho kong dala.', 'after_hours' => 9],
                ],
                'likes' => ['julia.navarro@reprocare.test', 'lorna.santos@reprocare.test', 'mariasanta@gmail.com'],
            ],
            [
                'author' => 'irene.tolentino@reprocare.test',
                'hours_ago' => 1560,
                'content' => "Tanong: ilang araw bago pwedeng magpatingin ulit after ng missed miscarriage? Ayoko pong hintayin ng tatlong buwan nang walang checkup.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang follow-up ay pwede pagkatapos ng dalawang linggo, at huwag hintayin ang regla para kumonsulta. libre ang counseling dito.', 'after_hours' => 4],
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Close ko rin ang experience na iyan. Hindi ka nag-iisa.', 'after_hours' => 10],
                ],
                'likes' => ['delia.cruz@reprocare.test', 'fe.mendoza@reprocare.test', 'carmen.reyes@reprocare.test', 'rosa.flores@reprocare.test'],
            ],
            [
                'author' => 'madonna@gmail.com',
                'hours_ago' => 2,
                'content' => "Sikat ang checkup ko ngayong umaga. Wala pang sampung minuto, tinawag na ako. Bago, ang tagal ng pila.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Nakakatulong ang online record at ang pagdating nang maaga. Salamat sa pagpapaalam sa amin!', 'after_hours' => 1],
                ],
                'likes' => ['maria.clara@example.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'delia.cruz@reprocare.test',
                'hours_ago' => 15,
                'content' => "May libreng dental checkup ba sa RHU? Masakit ang ngipin ko at buntis ako. Nahihiya akong magpatingin sa ngipin.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang impeksyon sa ngipin ay nakakaapekto sa buntis. Magpakita sa RHU para sa reseta at referral — huwag mong hayaang lumala.', 'after_hours' => 3],
                    ['author' => 'helen.bautista@reprocare.test', 'content' => 'Ako rin nagdusa noon, may municipal dental pala tuwing Martes.', 'after_hours' => 6],
                ],
                'likes' => ['rosa.flores@reprocare.test', 'lorna.santos@reprocare.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'maria.clara@example.test',
                'hours_ago' => 45,
                'content' => "Nag-post ako ng tip tungkol sa cycle tracker at marami kayong nag-like. Salamat! Ituloy ko ang pag-share ng mga natutunan ko sa RHU.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang mga ganitong post ang nakakatulong sa ibang nanay. Ituloy mo lang.', 'after_hours' => 2],
                ],
                'likes' => ['julia.navarro@reprocare.test', 'mariasanta@gmail.com', 'fe.mendoza@reprocare.test', 'delia.cruz@reprocare.test'],
            ],
            [
                'author' => 'julia.navarro@reprocare.test',
                'hours_ago' => 130,
                'content' => "Tip sa mga nagbibigay ng gatas: ang pag-stash ng breast milk ay kailangang malinis ang lalagyan at may tamang tag. Ako, nagtuturo pa lang.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Tama. Ang malamig na gatas ay tatagal ng tatlong araw sa ref at hanggang anim na buwan sa freezer. I-label ang petsa.', 'after_hours' => 4],
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Salamat, need ko ito balikan.', 'after_hours' => 9],
                ],
                'likes' => ['gloria.aquino@reprocare.test', 'carmen.reyes@reprocare.test', 'helen.bautista@reprocare.test'],
            ],
            [
                'author' => 'carmen.reyes@reprocare.test',
                'hours_ago' => 250,
                'content' => "May tanong: ilang araw ang normal na pagdurugo after ng delivery? Ako, anim na araw na at hindi pa rin tapos.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Hanggang 4-6 linggo ang lochia ay normal. Pero kung biglang dumami, may amoy, o may lagnat, ipakita agad ang sarili.', 'after_hours' => 2],
                ],
                'likes' => ['lorna.santos@reprocare.test', 'delia.cruz@reprocare.test', 'maria.clara@example.test'],
            ],
            [
                'author' => 'gloria.aquino@reprocare.test',
                'hours_ago' => 420,
                'content' => "Nagpapasalamat ako sa mga nanay na nagpa-checkup kahit ayaw ng asawa nila. Kayo ang pinaka-malakas dito.",
                'comments' => [
                    ['author' => 'ana@gmail.com', 'content' => 'Totoo. At kailanman, kausapin namin ang asawa para maintindihan niya ang halaga ng checkup.', 'after_hours' => 3],
                    ['author' => 'midwife', 'content' => 'Ang pangangalaga ng ina ay pangangalaga ng buong pamilya.', 'after_hours' => 5],
                ],
                'likes' => ['fe.mendoza@reprocare.test', 'irene.tolentino@reprocare.test', 'rosa.flores@reprocare.test', 'julia.navarro@reprocare.test'],
            ],
            [
                'author' => 'helen.bautista@reprocare.test',
                'hours_ago' => 660,
                'content' => "Nalaman ko lang dito na may free prenatal vitamins pala kahit hindi ka pa rehistro sa RHU. Pupunta ako bukas.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Oo, at libre rin ang rehistro. Dala lang ng ID o barangay clearance kung wala.', 'after_hours' => 2],
                    ['author' => 'ana@gmail.com', 'content' => 'Bukas ng 8am kami. Hayaan mo lang, hindi ka mahuhuli.', 'after_hours' => 4],
                ],
                'likes' => ['elena.garcia@reprocare.test', 'maria.clara@example.test', 'carmen.reyes@reprocare.test'],
            ],
            [
                'author' => 'madonna@gmail.com',
                'hours_ago' => 4,
                'content' => "Saan po kayo bumibili ng sanitaryo na mura? Nahihiya akong magtanong sa tindahan. May reusable pad ba kayong naroon?",
                'comments' => [
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'May reusable pad sa coop sa may plaza. Ang tagal ng dalawang taon at hugasan lang.', 'after_hours' => 3],
                    ['author' => 'midwife', 'content' => 'Palit-palin ang pad tuwing apat na oras kahit anong uri. Ang mahalaga ay malinis ang kamay.', 'after_hours' => 6],
                ],
                'likes' => ['lorna.santos@reprocare.test', 'fe.mendoza@reprocare.test', 'delia.cruz@reprocare.test', 'irene.tolentino@reprocare.test'],
            ],
            [
                'author' => 'rosa.flores@reprocare.test',
                'hours_ago' => 300,
                'content' => "Nag-log ako ng danger sign kahapon at agad akong tinawagan ng RHU. Salamat sa system na ito — napunta ako sa checkup nang hindi nila ako kinakausap personally.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ganyan dapat. Ang bawat report ay binabasa namin, at ang tawag ay para lang sa kaligtasan mo.', 'after_hours' => 2],
                    ['author' => 'ana@gmail.com', 'content' => 'Tumawag ka kahit gabi. Okay lang, para sa iyo.', 'after_hours' => 4],
                ],
                'likes' => ['julia.navarro@reprocare.test', 'maria.clara@example.test', 'helen.bautista@reprocare.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'mariasanta@gmail.com',
                'hours_ago' => 720,
                'content' => "May nagtatanong kung pwede akong magbakuna against flu habang nagbibigay ng gatas. Oo, sabi ng midwife ko.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ligtas ang flu vaccine habang nagpapasusog. Nakakatulong pa ito dahil dadaan ang antibodies sa gatas.', 'after_hours' => 3],
                ],
                'likes' => ['delia.cruz@reprocare.test', 'carmen.reyes@reprocare.test', 'fe.mendoza@reprocare.test'],
            ],
            [
                'author' => 'elena.garcia@reprocare.test',
                'hours_ago' => 180,
                'content' => "Nagpapaalala: ang pagbubuntis ay hindi sakit, pero ang hindi pagpatingin ay nakakasama. Apat na visit ang minimum sa buong pregnancy.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Tama: first trimester, second, third, at pagkatapos ng delivery. Kung may risk, mas madalas.', 'after_hours' => 2],
                    ['author' => 'ana@gmail.com', 'content' => 'May libre transport sa mga purok na malayo, itanong lang sa BHW.', 'after_hours' => 5],
                ],
                'likes' => ['maria.clara@example.test', 'lorna.santos@reprocare.test', 'helen.bautista@reprocare.test', 'irene.tolentino@reprocare.test', 'rosa.flores@reprocare.test'],
            ],
            [
                'author' => 'irene.tolentino@reprocare.test',
                'hours_ago' => 540,
                'content' => "Nahihiya akong magtanong dito pero sasabihin ko na lang: amoy na hindi ko gusto ang ulam ko ngayon, buntis ba ako? Ang tagal na naming nagpaplano.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Ang pagbabago ng amoy ay isang early sign, pero ang test lang ang sure. Pwede kang mag-home test o pumunta sa RHU para sa libreng confirmation.', 'after_hours' => 2],
                    ['author' => 'julia.navarro@reprocare.test', 'content' => 'Ako, amoy lang ng kanin ang hindi ko na kaya. Positive pala.', 'after_hours' => 7],
                ],
                'likes' => ['fe.mendoza@reprocare.test', 'delia.cruz@reprocare.test', 'gloria.aquino@reprocare.test'],
            ],
            [
                'author' => 'delia.cruz@reprocare.test',
                'hours_ago' => 900,
                'content' => "Nagpapasalamat sa community. Nang namatay ang bata namin, kayo ang nag-text. Hindi ko inaasahan na may mag-aalala.",
                'comments' => [
                    ['author' => 'midwife', 'content' => 'Hindi kami makakalimot sa inyo. Ang pangangalaga ay patuloy kahit pagkatapos ng pagkawala.', 'after_hours' => 1],
                    ['author' => 'elena.garcia@reprocare.test', 'content' => 'Nandito lang kami, Delia.', 'after_hours' => 3],
                ],
                'likes' => ['maria.clara@example.test', 'lorna.santos@reprocare.test', 'helen.bautista@reprocare.test', 'carmen.reyes@reprocare.test', 'ana@gmail.com'],
            ],
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
