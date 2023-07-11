<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Message;
use App\Jobs\SendUnreadMail; 

class CheckUnreadMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:unread_messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check unread messgaes in 10 minutes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenMinutesAgo = now()->subMinutes(10);

        // Retrieve the unseen messages older than 10 minutes and email not sent
        $unseenMessages = Message::where('is_seen', 0)
    ->where('created_at', '<=', $tenMinutesAgo)
    ->where('is_notified_email', 0)
    ->whereNotNull('message')
    ->where(function ($query) {
        $query->where('message', 'not regexp', '^upload_ids:|upload_ids:[0-9]+\s')
            ->where('message', 'not regexp', '^service_packages_custom_id:|service_packages_custom_id:[0-9]+\s');
    })
    ->get();


        if ($unseenMessages->count() > 0) {
            // Retrieve the first unseen message to get the sender user and conversation user
            $firstUnseenMessage = $unseenMessages->first();
            $senderUser = User::find($firstUnseenMessage->user_id);
            $conversationUser = User::find($firstUnseenMessage->conversation_id);

            if ($senderUser && $conversationUser) {
                $messages = $unseenMessages->pluck('message')->toArray();

                // Send the email using the updated unread.blade.php template
                Mail::send('emails.unread', ['senderUser' => $senderUser, 'conversationUser' => $conversationUser, 'messages' => $messages], function ($message) use ($senderUser, $conversationUser) {
                    $message->to($conversationUser->email);
                    $message->subject("You've received messages from {$senderUser->username}");
                });

                // Mark all unseen messages as email sent by setting is_notified_email to 1
                $unseenMessages->each(function ($message) {
                    $message->is_notified_email = 1;
                    $message->save();
                });
            }
        }
    }
}
