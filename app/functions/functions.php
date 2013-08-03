<?php
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org)
*/
function isLoggedIn() {
	return false;
}
function returnApiMessage($message) {
	return json_encode($message);
}
function shutdown() { //terminate program, print the errors
   $last_error = error_get_last();
   if( $last_error != NULL ){
      print_r ($last_error);
      echo 'Unknown exception, please try again.';
   }
}
function proc_timeout($start) { //check if program has timed out
    return time() - $start > 15 ? true : false; //current timeout, 15s
}
function proc_exec($cmd, $inputs) {
    $starttime = time();
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );
    $process = proc_open($cmd, $descriptorspec, $pipes, NULL, $_ENV);
    if (is_resource($process)) {
        list($in, $out, $err) = $pipes;
        stream_set_blocking( $in, true ); 
        stream_set_blocking( $out, false ); 
        stream_set_blocking( $err, false ); 
        if( strlen($inputs) > 0 ){
           //pass stdin
           foreach( explode("\n", $inputs) as $a ){
               $inputlist = $a . "\n";
               fwrite($in, $inputlist);
           }
        }
        fclose($in);
        //read output
        $stdout = '';
        while(!feof($out) && !proc_timeout($starttime)){ 
           $stdout = fgets($out, 128); 
           echo nl2br(htmlentities($stdout));
        }    
        fclose($out);
        $stderr = '';
        while(!feof($err) && !proc_timeout($starttime)){ 
           $stderr = fgets($err, 128); 
           echo nl2br(htmlentities($stderr));
        }    
        GLOBAL $processname;
        while(true){ //check if process is still running
           $status = proc_get_status( $process );
           if($status['running'] && proc_timeout($starttime)){
              echo "Sorry. system timeout!<br>";
              proc_terminate($process);
              break;
              #$retval = proc_close($process);
           }else if(!$status['running']){
              break;
           }
           sleep(1);
        }
        $retval = proc_close($process);
    }
}
?>