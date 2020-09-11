<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;

class UpdateConversations extends Command
{
    protected $signature = 'conversations:update {conversation} {--y|yes}';

    protected $description = 'Update a specific conversation';

    public function handle()
    {
        $conversationName = $this->argument('conversation');

        if ($this->option("yes")) {
            $continue = true;
        } else {
            $continue = $this->confirm(
                sprintf(
                    'This will update %s conversation. Are you sure you want to continue?',
                    $conversationName
                )
            );
        }

        if ($continue) {
            $this->importConversation($conversationName);

            $this->info('Imports finished');
        } else {
            $this->info('OK, not running');
        }
    }

    protected function importConversation($conversationName): void
    {
        $this->info(sprintf('Importing conversation %s', $conversationName));

        $filename = base_path("resources/conversations/$conversationName.conv");
        $model = file_get_contents($filename);

        $newConversation = Conversation::firstOrNew(['name' => $conversationName]);
        $newConversation->fill(['model' => $model]);
        $newConversation->save();

        $this->info(sprintf('Activating conversation with name %s', $newConversation->name));
        $newConversation->activateConversation();
    }
}