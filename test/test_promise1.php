<?php
/*
    This script test teh creation of a promise from the Associated Deferred
 * 
 */

require_once('../vendor/autoload.php');

/*
    This function returns a promise with a string as resolve value
 * 
 */

$loop = React\EventLoop\Factory::create();


/*
 * Start the show
 */
echo "welcome to promise tes".PHP_EOL;

$promi = getPromise( $loop );

$promi->then(
    function ( $resolution ) {
        
        echo "In onFulfilled".PHP_EOL;
        echo $resolution.PHP_EOL;
    },
    
    function ( $cagamo ) {
        
        echo "In onRejected".PHP_EOL;
    }
    );


$loop->run();
echo "finished\n";



function getPromise( $loop ) : React\Promise\PromiseInterface
{
    $deferred = new  React\Promise\Deferred();
 
    
    $timer = $loop->addTimer(5, function () use ($deferred) {
        
        echo "Resolving\n";
        $deferred->resolve("SUCESS Ha Ha Ha ");
        
        //     $deferred->reject();
    });
        
    
    echo "in getPromise()".PHP_EOL;
 
    
    return $deferred->promise();
}
