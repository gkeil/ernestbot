<?php

require_once('../vendor/autoload.php');

require_once('../RestRequest/RestRequest.php');

// create event loop
$loop = \React\EventLoop\Factory::create();


// create object

$webreq = new \RestRequest\RestRequest($loop,
                                        'https://xxopentdb.com/api.php?amount=1&difficulty=easy&type=multiple');

$webreq->makeRequest()->then(
    
    function ( $answer ) {              // if promise resolves
        echo "Promise resolved sucessfully".PHP_EOL;  
        echo $answer.PHP_EOL;  
    },
    
    function ( \Exception $e ) {        // if project fails
        echo "Promise Failed".PHP_EOL;
        echo $e->getMessage();    
    }
    
    );

/*
 * Start event loop
 */
$loop->run();