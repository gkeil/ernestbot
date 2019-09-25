<?php
// /rootBot/commands/misc/hello.php

// Livia forces you to use lowercase command name and group ID.
// (misc = group ID, hello = command name)

// Livia will automatically call the anonymous function and pass the LiviaClient instance.
return function ($client) {
    // Extending is required
    return (new class($client) extends \CharlotteDunois\Livia\Commands\Command {
        function __construct(\CharlotteDunois\Livia\Client $client) {
            parent::__construct($client, array(
                'name' => 'hello',
                'aliases' => array(),
                'group' => 'misc',
                'description' => 'Replies to hello salutation.',
                'guildOnly' => false
            ));
            echo "In the command Contructor of Hello".PHP_EOL;
            
        }
        
        // Checks if the command is allowed to run - the default method from Command class also checks userPermissions.
        // Even if you don't use all arguments, you are forced to match that method signature.
        
        //function hasPermission(\CharlotteDunois\Livia\Commands\Context $context, bool $ownerOverride = true) {
        //    // return $context->message->member->roles->has('SERVER_STAFF_ROLE_ID');
        //    return true;
        //}
        
        // Even if you don't use all arguments, you are forced to match that method signature.
        function run(   \CharlotteDunois\Livia\Commands\Context $context, 
                        \ArrayObject $args,
                        bool $fromPattern) 
        {
                // Do what the command has to do.
                // You are free to return a Promise, or do all-synchronous tasks synchronously.
                
                // If you send any messages (doesn't matter how many),
                // return (resolve) the Message instance, or an array of Message instances.
                // Promises are getting automatically resolved.
                echo "In the command run()".PHP_EOL;
                $context->reply("Hello old boy!!");
                
        }
    });
};