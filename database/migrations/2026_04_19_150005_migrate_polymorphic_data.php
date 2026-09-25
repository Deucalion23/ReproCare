<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a mapping of old user IDs to new role-specific IDs
        $userToMidwife = DB::table('users')->where('role', 'midwife')->pluck('id')->mapWithKeys(function($userId) {
            $midwife = DB::table('midwives')->where('email', DB::table('users')->where('id', $userId)->value('email'))->first();
            return [$userId => $midwife->id];
        });

        $userToPatient = DB::table('users')->where('role', 'user')->pluck('id')->mapWithKeys(function($userId) {
            $patient = DB::table('patients')->where('email', DB::table('users')->where('id', $userId)->value('email'))->first();
            return [$userId => $patient->id];
        });

        $userToBhw = DB::table('users')->where('role', 'bhw')->pluck('id')->mapWithKeys(function($userId) {
            $bhw = DB::table('bhws')->where('email', DB::table('users')->where('id', $userId)->value('email'))->first();
            return [$userId => $bhw->id];
        });

        // Migrate checkups
        DB::table('checkups')->orderBy('id')->chunk(100, function($checkups) use ($userToPatient, $userToMidwife, $userToBhw) {
            foreach ($checkups as $checkup) {
                $checkup->patient_id = $userToPatient->get($checkup->user_id_old);
                $checkup->patient_type = 'App\\Models\\Patient';
                
                $midwifeId = $userToMidwife->get($checkup->midwife_user_id_old);
                if ($midwifeId) {
                    $checkup->midwife_id = $midwifeId;
                    $checkup->midwife_type = 'App\\Models\\Midwife';
                }
                
                $scheduledById = $userToMidwife->get($checkup->scheduled_by_id_old) ?? $userToBhw->get($checkup->scheduled_by_id_old);
                if ($scheduledById) {
                    $checkup->scheduled_by_id = $scheduledById;
                    $role = DB::table('users')->where('id', $checkup->scheduled_by_id_old)->value('role');
                    $checkup->scheduled_by_type = $role === 'midwife' ? 'App\\Models\\Midwife' : 'App\\Models\\Bhw';
                }
                
                DB::table('checkups')->where('id', $checkup->id)->update([
                    'patient_id' => $checkup->patient_id,
                    'patient_type' => $checkup->patient_type,
                    'midwife_id' => $checkup->midwife_id,
                    'midwife_type' => $checkup->midwife_type,
                    'scheduled_by_id' => $checkup->scheduled_by_id,
                    'scheduled_by_type' => $checkup->scheduled_by_type,
                ]);
            }
        });

        // Migrate health records
        DB::table('health_records')->orderBy('id')->chunk(100, function($records) use ($userToPatient, $userToMidwife, $userToBhw) {
            foreach ($records as $record) {
                $record->patient_id = $userToPatient->get($record->user_id_old);
                $record->patient_type = 'App\\Models\\Patient';
                
                $recordedById = $userToMidwife->get($record->recorded_by_id_old) ?? $userToBhw->get($record->recorded_by_id_old) ?? $userToPatient->get($record->recorded_by_id_old);
                if ($recordedById) {
                    $record->recorded_by_id = $recordedById;
                    $role = DB::table('users')->where('id', $record->recorded_by_id_old)->value('role');
                    if ($role === 'midwife') {
                        $record->recorded_by_type = 'App\\Models\\Midwife';
                    } elseif ($role === 'bhw') {
                        $record->recorded_by_type = 'App\\Models\\Bhw';
                    } else {
                        $record->recorded_by_type = 'App\\Models\\Patient';
                    }
                }
                
                DB::table('health_records')->where('id', $record->id)->update([
                    'patient_id' => $record->patient_id,
                    'patient_type' => $record->patient_type,
                    'recorded_by_id' => $record->recorded_by_id,
                    'recorded_by_type' => $record->recorded_by_type,
                ]);
            }
        });

        // Migrate pregnancies
        DB::table('pregnancies')->orderBy('id')->chunk(100, function($pregnancies) use ($userToPatient) {
            foreach ($pregnancies as $pregnancy) {
                $pregnancy->patient_id = $userToPatient->get($pregnancy->user_id_old);
                $pregnancy->patient_type = 'App\\Models\\Patient';
                
                DB::table('pregnancies')->where('id', $pregnancy->id)->update([
                    'patient_id' => $pregnancy->patient_id,
                    'patient_type' => $pregnancy->patient_type,
                ]);
            }
        });

        // Migrate notifications
        DB::table('notifications')->orderBy('id')->chunk(100, function($notifications) use ($userToPatient, $userToMidwife, $userToBhw) {
            foreach ($notifications as $notification) {
                $userId = $notification->user_id_old;
                $role = DB::table('users')->where('id', $userId)->value('role');
                
                if ($role === 'midwife') {
                    $notification->user_id = $userToMidwife->get($userId);
                    $notification->user_type = 'App\\Models\\Midwife';
                } elseif ($role === 'bhw') {
                    $notification->user_id = $userToBhw->get($userId);
                    $notification->user_type = 'App\\Models\\Bhw';
                } else {
                    $notification->user_id = $userToPatient->get($userId);
                    $notification->user_type = 'App\\Models\\Patient';
                }
                
                DB::table('notifications')->where('id', $notification->id)->update([
                    'user_id' => $notification->user_id,
                    'user_type' => $notification->user_type,
                ]);
            }
        });

        // Migrate forum posts
        DB::table('forum_posts')->orderBy('id')->chunk(100, function($posts) use ($userToPatient, $userToMidwife, $userToBhw) {
            foreach ($posts as $post) {
                $userId = $post->user_id_old;
                $role = DB::table('users')->where('id', $userId)->value('role');
                
                if ($role === 'midwife') {
                    $post->user_id = $userToMidwife->get($userId);
                    $post->user_type = 'App\\Models\\Midwife';
                } elseif ($role === 'bhw') {
                    $post->user_id = $userToBhw->get($userId);
                    $post->user_type = 'App\\Models\\Bhw';
                } else {
                    $post->user_id = $userToPatient->get($userId);
                    $post->user_type = 'App\\Models\\Patient';
                }
                
                DB::table('forum_posts')->where('id', $post->id)->update([
                    'user_id' => $post->user_id,
                    'user_type' => $post->user_type,
                ]);
            }
        });

        // Migrate forum comments
        DB::table('forum_comments')->orderBy('id')->chunk(100, function($comments) use ($userToPatient, $userToMidwife, $userToBhw) {
            foreach ($comments as $comment) {
                $userId = $comment->user_id_old;
                $role = DB::table('users')->where('id', $userId)->value('role');
                
                if ($role === 'midwife') {
                    $comment->user_id = $userToMidwife->get($userId);
                    $comment->user_type = 'App\\Models\\Midwife';
                } elseif ($role === 'bhw') {
                    $comment->user_id = $userToBhw->get($userId);
                    $comment->user_type = 'App\\Models\\Bhw';
                } else {
                    $comment->user_id = $userToPatient->get($userId);
                    $comment->user_type = 'App\\Models\\Patient';
                }
                
                DB::table('forum_comments')->where('id', $comment->id)->update([
                    'user_id' => $comment->user_id,
                    'user_type' => $comment->user_type,
                ]);
            }
        });

        // Migrate forum likes
        DB::table('forum_likes')->orderBy('id')->chunk(100, function($likes) use ($userToPatient, $userToMidwife, $userToBhw) {
            foreach ($likes as $like) {
                $userId = $like->user_id_old;
                $role = DB::table('users')->where('id', $userId)->value('role');
                
                if ($role === 'midwife') {
                    $like->user_id = $userToMidwife->get($userId);
                    $like->user_type = 'App\\Models\\Midwife';
                } elseif ($role === 'bhw') {
                    $like->user_id = $userToBhw->get($userId);
                    $like->user_type = 'App\\Models\\Bhw';
                } else {
                    $like->user_id = $userToPatient->get($userId);
                    $like->user_type = 'App\\Models\\Patient';
                }
                
                DB::table('forum_likes')->where('id', $like->id)->update([
                    'user_id' => $like->user_id,
                    'user_type' => $like->user_type,
                ]);
            }
        });

        // Migrate cycles
        DB::table('cycles')->orderBy('id')->chunk(100, function($cycles) use ($userToPatient) {
            foreach ($cycles as $cycle) {
                $cycle->patient_id = $userToPatient->get($cycle->user_id_old);
                $cycle->patient_type = 'App\\Models\\Patient';
                
                DB::table('cycles')->where('id', $cycle->id)->update([
                    'patient_id' => $cycle->patient_id,
                    'patient_type' => $cycle->patient_type,
                ]);
            }
        });

        // Migrate menstruation dailies
        DB::table('menstruation_dailies')->orderBy('id')->chunk(100, function($dailies) use ($userToPatient) {
            foreach ($dailies as $daily) {
                $daily->patient_id = $userToPatient->get($daily->user_id_old);
                $daily->patient_type = 'App\\Models\\Patient';
                
                DB::table('menstruation_dailies')->where('id', $daily->id)->update([
                    'patient_id' => $daily->patient_id,
                    'patient_type' => $daily->patient_type,
                ]);
            }
        });

        // Migrate fertility logs
        DB::table('fertility_logs')->orderBy('id')->chunk(100, function($logs) use ($userToPatient) {
            foreach ($logs as $log) {
                $log->patient_id = $userToPatient->get($log->user_id_old);
                $log->patient_type = 'App\\Models\\Patient';
                
                DB::table('fertility_logs')->where('id', $log->id)->update([
                    'patient_id' => $log->patient_id,
                    'patient_type' => $log->patient_type,
                ]);
            }
        });

        // Migrate preventive interventions
        DB::table('preventive_interventions')->orderBy('id')->chunk(100, function($interventions) use ($userToPatient) {
            foreach ($interventions as $intervention) {
                $intervention->patient_id = $userToPatient->get($intervention->user_id_old);
                $intervention->patient_type = 'App\\Models\\Patient';
                
                DB::table('preventive_interventions')->where('id', $intervention->id)->update([
                    'patient_id' => $intervention->patient_id,
                    'patient_type' => $intervention->patient_type,
                ]);
            }
        });

        // Migrate messages
        DB::table('messages')->orderBy('id')->chunk(100, function($messages) use ($userToPatient, $userToMidwife, $userToBhw) {
            foreach ($messages as $message) {
                // Sender
                $senderId = $message->sender_id_old;
                $senderRole = DB::table('users')->where('id', $senderId)->value('role');
                if ($senderRole === 'midwife') {
                    $message->sender_id = $userToMidwife->get($senderId);
                    $message->sender_type = 'App\\Models\\Midwife';
                } elseif ($senderRole === 'bhw') {
                    $message->sender_id = $userToBhw->get($senderId);
                    $message->sender_type = 'App\\Models\\Bhw';
                } else {
                    $message->sender_id = $userToPatient->get($senderId);
                    $message->sender_type = 'App\\Models\\Patient';
                }
                
                // Receiver
                $receiverId = $message->receiver_id_old;
                $receiverRole = DB::table('users')->where('id', $receiverId)->value('role');
                if ($receiverRole === 'midwife') {
                    $message->receiver_id = $userToMidwife->get($receiverId);
                    $message->receiver_type = 'App\\Models\\Midwife';
                } elseif ($receiverRole === 'bhw') {
                    $message->receiver_id = $userToBhw->get($receiverId);
                    $message->receiver_type = 'App\\Models\\Bhw';
                } else {
                    $message->receiver_id = $userToPatient->get($receiverId);
                    $message->receiver_type = 'App\\Models\\Patient';
                }
                
                DB::table('messages')->where('id', $message->id)->update([
                    'sender_id' => $message->sender_id,
                    'sender_type' => $message->sender_type,
                    'receiver_id' => $message->receiver_id,
                    'receiver_type' => $message->receiver_type,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \Exception('Rollback not supported. Restore from database backup instead.');
    }
};
