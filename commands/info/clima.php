<?php

/**
 * rootbot/commands/info/clima.php
 * 
 * This command provides wheather conditions for a give city in Argentina.
 * It sends an http GET request to the SMN API ( SMN = Servicio Meteorologico Ncional )
 * The info of the over 200 wheather stations is received in JSON format.
 * The city is requested in the 1st arg of clima command. The provided city is
 * seached as substring of the reported cities.
 * Data from smn is received in chunks. Info is presented when the last chunk is received 
 * and the 'end' event is emitted from the Response stream.
 * 
 */

// /rootBot/commands/moderation/ban.php

// Livia forces you to use lowercase command name and group ID.
// (moderation = group ID, ban = command name)

// Livia will automatically call the anonymous function and pass the LiviaClient instance.
return function ($client) 
{
    // Extending is required
    return (
        new class($client) extends \CharlotteDunois\Livia\Commands\Command {
         
            
        /**
         * Properties
         * 
         */    
        // Servicio Meteorologico Nacional URL
        private $url = "https://ws.smn.gob.ar/map_items/weather";
        private const MaxFields = 20;
        private $message;
        private $cooked_args;
            
            
        /**
         * Command Class constructor.
         * Command options are passed to teh parent class contructor in the options array.
         * 
         * {@inheritDoc}
         * @see \CharlotteDunois\Livia\Commands\Command::__construct()
         */    
        function __construct(\CharlotteDunois\Livia\Client $client) 
        {
            /**
             * Call parent constructor and pass array of options 
             * 
             */
            parent::__construct($client, array(
                'name' => 'clima',                                  // command name. Allways lowercase
                'aliases' => array('wheather'),                     // alias command names
                'group' => 'info',                                  // command group. Allways in lowercase
                'description' => 'Provides weather conditions.',    // Command Description
                'guildOnly' => false,                               // allow to respond in server and in direct message
                'throttling' => array(                              // Throttling is per-user
                    'usages' => 3,                                      // xx ussages      
                    'duration' => 3                                     // in yy seconds
                ),
                // * ARGUMENTS *
                'args' => array(
                    array(
                        'key' => 'city',                            // Argument name
                        'prompt' => 'Which to report wheather?',    // String to request it to user
                        'type' => 'string'                          // Argument type
                        )
                    )   // end array of commands
                )   // end array of options
                
            );  // end call to parant constructor
            
            echo "In the command Contructor of Clima".PHP_EOL;
            
        }   // end __contruct()
        
        
        
        /**
         * function run()
         * 
         * This function gets call when the command is invoqued.
         * The function receives 2 args:
         *      $context: a context obj that includes references to the Message, Client, etc
         *      $args: ArrayObject with teh argumenst passed to the command
         * 
         * Even if you don't use all arguments, you are forced to match that method signature.
         * 
         * {@inheritDoc}
         * @see \CharlotteDunois\Livia\Commands\Command::run()
         */
        function run(   \CharlotteDunois\Livia\Commands\Context $context, 
                        \ArrayObject $args,
                        bool $fromPattern           ) 
        {
            // Do what the command has to do.
            // You are free to return a Promise, or do all-synchronous tasks synchronously.
            
            // If you send any messages (doesn't matter how many),
            // return (resolve) the Message instance, or an array of Message instances.
            // Promises are getting automatically resolved.
            
                       
            /******************************************
             * If we are here, then exceute the command
             * 
             */
            
            
            /*
             * Set properties to $context
             */
            $this->message = $context->message;
            $this->cooked_args = $args;
            
            // Start the Typing indicator
            $this->message->channel->startTyping();    
                        
            // get the React event loop.
            $loop = $this->message->client->getLoop();
            
            /*
             * The command is processed by requesting info to an external
             * web service. Communication with teh web service is done
             * using the ReactHttp client
             * 
             */
            
            try
            {
                // Create HTTP client object
                $smnclient = new React\HttpClient\Client($loop);
                
                // prepare Request
                $request = $smnclient->request('GET', $this->url);
                
                // handle 'response' event 
                // 'request' implements writablestream. So handle on response event
                $request->on(   'response',
                    
                    function (React\HttpClient\Response $response)
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
                            // Use the received info to create the reply.
                            $this->replyinfo($answer);
                            }
                        );  // end response on 'end'
                    }    
                );      // end on response
                
                // handle 'error' event
                // handle any error that may occur
                $request->on('error', function (\Exception $e) {
                    echo $e->getMessage();      // so far only show message on console
                });
                    
                /*
                 * OK. All the call backs sets to handle the events.
                 * So Actually send the request 
                 */
                $request->end();
                    
                    
            // Trap all errors here
            //
            } catch ( Exception $e) {
                echo $e->getMessage();      // so far only show message on console
            }
            
            
                
        }   // end run()
            

            
        
        /**
         * replyinfo()
         * 
         * This function accepts the Json string obtained from the url
         * and conforms the output.
         * The city argument is searched as substring in teh list of cities
         * provided in the Json.
         * Both city argument and the cities on list are normalized before the
         * search. 
         * 
         * @param string $answer
         */
        private function replyinfo(string $answer)
        {
            $cities_info = array();  // array holding wheather info of matching cities
            
            
            // convert Json into array
            $data = json_decode($answer, true);
            
            // search the response for the requested city
            // all cities matching the request are reported
            foreach ( $data as $city )
            {
                // check requested city against city name
                
                // get the strings with non accent and lowercase
                $city_name   = $this->normalize_string( $city['name'] );
                $target_city = $this->normalize_string( $this->cooked_args->city);
                
                // search for the target city in the city name of this array element
                $pos = strpos( $city_name, $target_city );
                
                if ( $pos !== false)
                {
                    // the city name matches the request
                    $info = new stdClass();
                    
                    // TODO Add status as image
                    // extract city info
                    $info->name     = $city['name'];
                    $info->prov     = $city['province'];
                    $info->temp		= $city['weather']['temp'];
                    $info->humi		= $city['weather']['humidity'];
                    $info->st		= $city['weather']['st'];
                    $info->desc		= $city['weather']['description'];
                    
                    if ( !$info->st )	// validate ST
                        $info->st = $info->temp;
                        
                        // add to array
                        $cities_info[] = $info;
                        
                }
                
            }   // end for $data
            
            // check if we have at least 1 matching city
            if ( empty($cities_info) )
            {
                // inform that no city matches the request
                $this->message->channel->send( "No city matches request\n" );
            }
            else
            {
                // create embed with all the info
                $embed = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
                
                // Count the number of fields we are showing and keep under maximum
                $field_cnt = 0;
                
                // Build the embed
                $embed
                ->setTitle('Climate info')                              // Set a title
                ->setColor(0x5CACEE);                                   // Set a color (the thing on the left side)
                
                // add info of each matching city
                foreach($cities_info as $info )
                {
                    if ( $field_cnt < self::MaxFields )
                    {
                        // prepare string with city name and province
                        $name_str = $info->name." ( ".$info->prov." )";
                        
                        // prepare string with city climate info
                        $info_str = "";
                        $info_str .= "Temp: ".$info->temp." ST: ".$info->st.PHP_EOL;
                        $info_str .= "Cond: ".$info->desc." Humi: ".$info->humi.PHP_EOL;
                        
                        // add field
                        $embed
                        ->addField($name_str, $info_str);         // Add city climate info
                        
                        // increment field counter
                        $field_cnt++;
                    }
                }
                
                // add warning if the max fields was excedded
                if ( $field_cnt >= self::MaxFields )
                {
                    $embed
                    ->addField("Warning", "Only first ".self::MaxFields." matching cities are displayed".PHP_EOL);         // Add warning
                    
                }
                
                // finish embed
                $embed
                ->setFooter('Data from Servicio Metorologico Nacional');              // Set a footer without icon
                
                // clear typing indicator
                $this->message->channel->stopTyping();
                
                // Send the message
                
                // We do not need another promise here, so
                // we call done, because we want to consume the promise
                $this->message->channel->send('', array('embed' => $embed))
                ->done(null, function ($error) {
                                // We will just echo any errors for this example
                                echo $error.PHP_EOL;
                              }
                       );
                    
                    
            }
            
        }   // end replyinfo
        
       
        /**
         * nomalize_string()
         * converts to lowercase and eliminates accents
         * 
         * @param string $in
         * @return string
         */
        private function normalize_string( string $in ) : string
        {
            $search = array( 'á','é','í','ó','ú','ü','A','É','Í','Ó','Ú','Ü');
            $replace = array('a','e','i','o','u','u','A','E','I','O','U','U');
               
            return strtolower(str_replace($search, $replace, $in));
            
        }   // end nomalize_string
        
     
      
        
        // Checks if the command is allowed to run - the default method from Command class also checks userPermissions.
        // Even if you don't use all arguments, you are forced to match that method signature.
        
        //function hasPermission(\CharlotteDunois\Livia\Commands\Context $context, bool $ownerOverride = true) {
        //    return $context->message->member->roles->has('SERVER_STAFF_ROLE_ID');
        //}
        
            
    }   // end anonymous class
    );  // end return()
    
};  // end of anonymous function