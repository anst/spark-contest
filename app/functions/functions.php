<?php
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org)
*/
function isLoggedIn() {
	return true;
}
function returnApiMessage($message) {
	return json_encode($message);
}
function startsWith($haystack, $needle){
  return !strncmp($haystack, $needle, strlen($needle));
}
function shutdown() { //terminate program, print the errors
   $last_error = error_get_last();
   if( $last_error != NULL ){
      print_r ($last_error);
      echo 'Unknown exception, please try again.';
   }
}
function proc_timeout($start) { //check if program has timed out
    return microtime(true) - $start > 15 ? true : false; //current timeout, 15s
}
function proc_exec($cmd, $inputs, $type) {
	$output = "";
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );
    $process = proc_open($cmd, $descriptorspec, $pipes, NULL, $_ENV);
    $starttime = microtime(true);
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
           //$output .= nl2br(htmlentities($stdout));
           $output .= $stdout;
        }    
        fclose($out);
        $stderr = '';
        while(!feof($err) && !proc_timeout($starttime)){ 
           $stderr = fgets($err, 128); 
           //$output .=  nl2br(htmlentities($stderr));
           $output .= $stderr;
        }
        $error = false;
        $error_message = "";
        GLOBAL $processname;
        while(true){ //check if process is still running
           $status = proc_get_status( $process );
           if($status['running'] && proc_timeout($starttime)){
              $error_message = "Your program timed out. Please make sure you are under the time limit.";
              $error = true;
              proc_terminate($process);
              break;
              #$retval = proc_close($process);
           }else if(!$status['running']){
              break;
           }
           sleep(1);
        }
        $retval = proc_close($process);
        return $error?["success"=>"false","error"=>$error_message]:["success"=>"true", "type"=>$type, "id"=>"lolwut", "timestamp"=>date("Y-m-d H:i:s"), "time"=>round(microtime(true)-$starttime,2), "output"=>$output];
    }
}
function compileProgram($sourcefile, $sourcedir, $classfile, $class, $inputs, $args, $processname, $data) {
	@mkdir($sourcedir, 0755, true);
	$outputfile = "$classfile";
	chdir("/tmp/$processname");
	$handle = fopen($sourcefile, 'w+');
	fwrite($handle, $data); 
	fclose($handle);
	$compile_error = false;
	$runtime_error = false;
	$compile_data = proc_exec("javac -cp . {$sourcefile} 2>&1", $inputs, "compile");
	if($compile_data["output"]!="") $compile_error = true; 
	$exec_data = proc_exec("java -classpath $sourcedir $class $args", $inputs, "execute");
  if ($exec_data["success"]==="false") return ["success"=>"false", "error"=>"Your program ran longer than the time alotted!"];
  if(preg_match("/.?Exception in thread \"[\d\w]+\" [\d\w]+\.?.+\.?\n\t[\d\w]{2}\s[\d\w]+\.[\d\w]+\([\d\w]+\.?[\d\w]+:\d+\)/",$exec_data["output"])==1) $runtime_error = true;
	exec("rm -rf /tmp/$processname*");//remove directory subject to change
	if($compile_data["success"]==="true"&&$exec_data["success"]==="true"&&$compile_error==false&&$runtime_error==false) return ["success"=>"true","compile"=>$compile_data,"exec"=>$exec_data];
	else if ($compile_error) return ["success"=>"false", "error"=>"Your program was unable to compile! Please check for syntax errors and resubmit."];
	else if ($runtime_error) return ["success"=>"false", "error"=>"Your program encountered an error while running! Please check your program logic and resubmit."];
  else return ["success"=>"false", "error"=>"Unknown error, something huge has gone wrong!"];

}
function proc_safety() {
	$max_proc_count = exec("/bin/bash -c \"ulimit -u 2>&1\""); //stop abuse of compilation, limit processes
	$processes = array();

	exec("/bin/bash -c \"ps -fhu ".  get_current_user() ." | grep /tmp/| awk -F/ '{print $3} 2>&1'\"", $processes); //change username to allow ps to work
	if(count($processes) > ($max_proc_count - 20)) {
	   return "Please try in a moment, server is overloaded.";
	}
	try{ //check if we've run out of memory, update the amount of memory needed to run
	  ini_set('memory_limit', (ini_get('memory_limit')+1).'M');
	} catch(Exception $e){
	    return "Server out of memory, please try again in a little bit.";
	}
	return "Success";
}

?>