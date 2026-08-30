<?php

namespace App\Console\Commands;

use App\Jobs\SendUnreadMessageRemindersJob;
use Illuminate\Console\Command;

class SendUnreadMessageRemindersCommand extends Command
{
    protected $signature = 'messages:send-unread-reminders';
    protected $description = 'Send digest email reminders for unread messages based on user preferences';

    public function handle(): int
    {
        $this->info('Processing unread message reminders...');
        $job = new SendUnreadMessageRemindersJob;
        $sent = $job->handle();
        $this->info("Sent {$sent} unread message reminder digests.");

        return self::SUCCESS;
    }
}
