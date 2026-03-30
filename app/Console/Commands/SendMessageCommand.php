<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a message to the broadcast channel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('What is your name?');
        $text = $this->ask('What is your message?');

        // Dispatch the event
        \App\Events\MessageSent::dispatch($name, $text);

    }
}
