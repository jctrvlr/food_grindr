<?php
/* 
* sendLogs
*
* Send logs to logging server using rabbitMQ. 
* 
* @param {Array} $options
*   @param {string} - $options[0] - Level of log event (Error, Info, Debug)
*   @param {string} - $options[1] - Origin of log
*   @param {string} - $options[2] - Error/event message
*
* @return void
*/
function sendLogs($options){
    switch (strtolower($options[0])) {
        case 'e':
        case 'error':
            $level='ERROR';
            break;
        case 'i':
        case 'info':
            $level='INFO';
            break;
        case 'd':
        case 'debug':
            $level='DEBUG';
            break;
        default:
            $level='INFO';
    }

    $client = new rabbitMQClient("logger.ini","testServer");
    $t = time();
    // ARRAY $options - type, location, output, more? - TODO 
    $msg = date("m/d/Y h:i:s A", $t) . " --- [" . $level . "] -- " . $options[1] . " -- " . $options[2] . PHP_EOL;
    
    $request = array();
    $request['type'] = "log_event";
    $request['level'] = $level;
    $request['loc'] = $options[1];
    $request['message'] = $msg;
    $response = $client->publish($request);

    // Create local log file labeled by day, in logs folder inside where file is being executed
    // Appends if file exists
    file_put_contents('./logs/log_'.date("j.n.Y").'.txt', $msg, FILE_APPEND);
    
    echo "Log file created";

}

?>