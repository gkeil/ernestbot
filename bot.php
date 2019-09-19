<?php

/**
 * ERNESTBOT.
 * 
 * This is a multiple functions bot based on the LIVIA framework
 * @author Guillermo Keil
 */

require_once(__DIR__.'/vendor/autoload.php');

/**
 * Array holding sconfiguration values.
 * 
 * This array holds configuration option and sesitive data such Bot token and owners IDs
 * 
 * @var array $config
 */
$config = array();
$config = json_decode(file_get_contents(__DIR__.'/config.json'), true);


$loop = \React\EventLoop\Factory::create();

try {

    /*
     * Create the Client and specify the bot owners
     *
     * TODO add invite argument in client creation
     */
    $client = new \CharlotteDunois\Livia\Client(
        array(                                      // Client creations options
            'owners' => array(
                $config["ID_owner1"],
                $config["ID_owner2"]
                ),
            'unknownCommandResponse' => false
        ), 
        $loop);                                     // event loop

    /*
     * Registers default commands, command groups and argument types
     */
    $client->registry->registerDefaultGroups()
                     ->registerDefaultCommands()
                     ->registerDefaultTypes();

    /*
     * Register groups of commands.
     * Command files are located in the subfolder with name 'id'
     * These subfolder are located in the commands folder specified in config.json
     */
    $client->registry
        ->registerGroup(array(
            'id' => 'info',
            'name' => 'Info' ))
        ->registerGroup(array(
            'id' => 'misc',
            'name' => 'Miscalanea' ));

    /*
     * Register the commands located in the subfolder un der the commands folder
     */
    $client->registry->registerCommandsIn(__DIR__ . $config['cmd_folder']);

    /*
     * Setup the callbacks for Client ready and error events
     *
     */
    
    // on ready
    $client->on('ready', function () use ($client) {
        echo    'Logged in as ' . $client->user->tag . 
                ' created on '  . $client->user->createdAt->format('d.m.Y H:i:s') . PHP_EOL;
    });

    // on error
    $client->on('error', function (\Throwable $e) use ($client) {
        echo    'Error Found: ' . $e->getMessage() . PHP_EOL;
    });

    
    /*
     * Login to Discord
     */
    $client->login($config['token'])->done();
    
    /*
     * Start event loop
     */
    $loop->run();
}
/*
 * Catch any error that may occur during Bot initialization
 */
catch ( \Exception $e)
{
    echo $e->getMessage();
}