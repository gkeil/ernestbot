<?php
namespace RestRequest;

require_once('../vendor/autoload.php');

/**
 * 
 * This class is a helper to make http Rest Full request
 * the usage is:
 * 
 *  $webreq = new \RestRequest\RestRequest('<end point url>');
 *  
 *  $webreq->makeRequest->then(
 *                              function (),    // onfulfilled
 *                              function()      // onreject
 *                              );
 *  
 * @author Guille
 *        
 */
final class RestRequest
{
    private $loop;                      // event loop          
    private $url;                       // End Point URL to request. 
    private $method = 'GET';            // request method
    private $deferred;                  // deferred for return promise.
    private $request;                   // http request from http client
    
    /**
     * 
     * @param \React\EventLoop\LoopInterface $loop
     * @param string $url
     * @param string $method defaults to GET
     */
    public function __construct( \React\EventLoop\LoopInterface $loop,  // event loop               
                                 string $url,                           // End Point URL to request.
                                 string $method = 'GET' )               // request method           
    {
        $this->loop = $loop;            
        $this->url  = $url;             
        $this->method = $method;        
        
        
        // Create a deferred to handle the promisse
        $this->deferred = new  \React\Promise\Deferred();
        
        /*
         * Setup the react callbacks structure to process the request
         * 
         */
        
        try
        {
            // Create HTTP client object
            $webclient = new \React\HttpClient\Client($loop);
            
            // prepare Request
            $this->request = $webclient->request($this->method, $this->url);
            
            // handle 'response' event
            // 'request' implements writablestream. So handle on response event
            $this->request->on(   'response',
                
                function ( \React\HttpClient\Response $response)
                {
                    // This variable will store and concatenate the segments of response
                    // as they are received
                    static $answer = "";
                    
                    // handle response segments
                    // Response implements readablestream, so handle on data event
                    
                    $response->on('data',   function ($chunk) use(&$answer) {
                        
                        // concatenate the just received chunk
                        $answer .= $chunk;
                        }
                        
                    ); // end response on 'data'
                    
                    $response->on('end',    function() use(&$answer) {
                        
                        // resolve the promise with this value.
                        $this->deferred->resolve($answer);
                        }
                        
                    );  // end response on 'end'
            }
            );      // end on response
            
            // handle 'error' event
            // handle any error that may occur
            $this->request->on('error', function (\Exception $e) {
                
                // rejest promise with received Exception
                echo "Error event in request".PHP_EOL;
                $this->deferred->reject( $e );
                
                echo $e->getMessage();    
            });
                
            
                
                
        // Trap all errors here
        //
        } catch ( \Exception $e) {
            echo "Error In constructor".PHP_EOL;
            echo $e->getMessage();      // so far only show message on console
        }
                   
        
    }   // end constructor

    /**
     * This method must be called to actually trigger the request 
     * of the web service.
     * it returns a Promise:
     *      resolves with a string containing the data received from teh server
     *      rejects with the Exception generated
     *      
     * @return \React\Promise\PromiseInterface
     */   
    function makeRequest() : \React\Promise\PromiseInterface
    {
        /*
         * OK. All the call backs sets to handle the events.
         * So Actually send the request
         */
        
        $this->request->end();
        
        // return the promise
        return $this->deferred->promise();
        
    }   // end makeRequest
    
    
}   // end class

