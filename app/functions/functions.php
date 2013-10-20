<?php
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org)
*/
function isLoggedIn() {
	return isset($_SESSION['team'])&&is_numeric($_SESSION['team']);
}
function contestHasStarted() {
  return true;
}
function register($team,$pass,$school,$division,$members) {
  $conn = mysqli_connect(host, user, pw, db);
  $team = mysqli_real_escape_string($conn, $team);
  $pass = mysqli_real_escape_string($conn, $pass);
  foreach ($members as &$member) {
    $member = mysqli_real_escape_string($conn, $member);
  }
  $hash = md5($pass);
  extract($members);
  $query = "SELECT password FROM teams WHERE team = '$team';";
  $result = mysqli_query($conn, $query);
  if(mysqli_num_rows($result)===0) {
    $query = "INSERT INTO teams (id, team, school, division, member1, member2, member3, password) VALUES (NULL, '$team', '$school','$division','$member1', '$member2', '$member3', '$hash')";

    mysqli_query($conn, $query);
    mysqli_close($conn);

    return returnApiMessage(['success'=>'Successfully registered!']);
  } else {
    mysqli_close($conn);
    return returnApiMessage(['error'=>'Team already exists!']);
  }

}
function login($team, $pass) {
  $conn = mysqli_connect(host, user, pw, db);
  $team = mysqli_real_escape_string($conn, $team);
  $pass = mysqli_real_escape_string($conn, $pass);

  $query = "SELECT password FROM teams WHERE team = '$team';";
  $result = mysqli_query($conn, $query);
  if(mysqli_num_rows($result)===0) {
    return returnApiMessage(['error'=>'Team does not exist!']);
    mysqli_close($conn);
  } else if(mysqli_num_rows($result)===1){
    $passw = mysqli_fetch_array($result, MYSQL_ASSOC);
    if(md5($pass)===$passw['password']) {
      startsession($team);
      return returnApiMessage(['success'=>'Logged In!']);
    } else {
      return returnApiMessage(['error'=>'Incorrect password!']);
    }
  } else {
    return returnApiMessage(['error'=>'Unknown Error!']);
  }
}
function logout() {
  session_destroy();
  header("Location: /");
}
function startsession($team) {
  $_SESSION['team'] = $team;
}
function getTeamNumber() {
  if(isLoggedIn())
    return ['team'=>intval($_SESSION['team'])];
  return ['error'=> 'You are not logged in!'];
}
function getWrittenScores() {
  return [];
}
function getScoreboard() {
  return [];
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

function proc_timeout($start,$problem_timeout) { //check if program has timed out
    return microtime(true) - $start > $problem_timeout ? true : false;
}
function proc_exec($cmd, $inputs, $type,$problem_timeout) {
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
        while(!feof($out) && !proc_timeout($starttime,$problem_timeout)){ 
           $stdout = fgets($out, 128); 
           //$output .= nl2br(htmlentities($stdout));
           $output .= $stdout;
        }    
        fclose($out);
        $stderr = '';
        while(!feof($err) && !proc_timeout($starttime,$problem_timeout)){ 
           $stderr = fgets($err, 128); 
           //$output .=  nl2br(htmlentities($stderr));
           $output .= $stderr;
        }
        $error = false;
        $error_message = "";
        GLOBAL $processname;
        while(true){ //check if process is still running
           $status = proc_get_status( $process );
           if($status['running'] && proc_timeout($starttime,$problem_timeout)){
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
function judge($output, $correct){
  $submission = array_map('trim', preg_split ('/$\R?^/m', $output));
  $judge = array_map('trim', preg_split ('/$\R?^/m', $correct));

  return sizeof(array_diff_assoc($submission, $judge)) === 0;
}
function compileProgram($sourcefile, $sourcedir, $classfile, $class, $inputs, $args, $processname, $data,$problem_number,$file_input_title,$file_input_data,$file_output_data,$problem_timeout) {
  $team = getTeamNumber()['team'];
	@mkdir($sourcedir, 0755, true);
	$outputfile = "$classfile";
	chdir("/tmp/$processname");
	$handle = fopen($sourcefile, 'w+');
	fwrite($handle, $data);
	fclose($handle);
  if($file_input_data!==""){
    $handle = fopen($file_input_title.'.in', 'w+');
    fwrite($handle, $file_input_data);
    fclose($handle);
  }
  $conn = mysqli_connect(host, user, pw, db);
  $data = mysqli_real_escape_string($conn, $data);
  mysqli_close($conn);
	$compile_error = false;
	$runtime_error = false;

	$compile_data = proc_exec("javac -cp . {$sourcefile} 2>&1", "", "compile",$problem_timeout);

	if($compile_data["output"]!="")
    $compile_error = true; 

  #chdir(dirname(__FILE__));
	#$exec_data = proc_exec("java -classpath ../classloader ContestJudge /tmp/$processname/ ".substr($classfile,0,strlen($classfile)-6), $inputs, "execute");

  $exec_data = proc_exec("java -classpath $sourcedir $class $args", $inputs, "execute",$problem_timeout);

  if ($exec_data["success"]==="false") {
    $conn = mysqli_connect(host, user, pw, db);
    $cur_time = date("Y-m-d H:i:s");
    $query = "INSERT INTO submissions (id, team, problem, time, subid, code, output, success, error) VALUES (NULL, '$team', '$problem_number','$cur_time','$processname','$data','', 'No', 'Timeout')";

    mysqli_query($conn, $query);
    mysqli_close($conn);
    return ["success"=>"false", "error"=>"Your program ran longer than the time alotted! Please make sure you don't go above the time limit. [Timeout error]"];
  }
  
  if(preg_match("/.?Exception in thread/",$exec_data["output"])==1)
    $runtime_error = true;

	exec("rm -rf /tmp/$processname*");
	//compilation successful 
  if($compile_data["success"]==="true"&&$exec_data["success"]==="true"&&$compile_error===false&&$runtime_error===false) {
      $conn = mysqli_connect(host, user, pw, db);
      $timestamp_a = $exec_data['timestamp'];
      $output_a = mysqli_real_escape_string($conn, $exec_data['output']);
      if(judge($file_output_data,$exec_data['output'])) { //correct answer
        $query = "INSERT INTO submissions (id, team, problem, time, subid, code, output, success, error) VALUES (NULL, '$team', '$problem_number','$timestamp_a','$processname','$data','$output_a', 'Yes', 'None')";

        mysqli_query($conn, $query);
        mysqli_close($conn);
        return ["success"=>"true","time"=>$exec_data['time']];
      } else { //incorrect answer
        $query = "INSERT INTO submissions (id, team, problem, time, subid, code, output, success, error) VALUES (NULL, '$team', '$problem_number','$timestamp_a','$processname','$data','$output_a', 'No', 'None')";

        mysqli_query($conn, $query);
        mysqli_close($conn);
        return ["success"=>"false", "error"=>"Your output was incorrect!"];
      }
  }   
  //compile error
	else if ($compile_error) {
    $conn = mysqli_connect(host, user, pw, db);
    $cur_time = date("Y-m-d H:i:s");
    $output_a = mysqli_real_escape_string($conn, $compile_data['output']);
    $query = "INSERT INTO submissions (id, team, problem, time, subid, code, output, success, error) VALUES (NULL, '$team', '$problem_number','$cur_time','$processname','$data','$output_a', 'No', '')";

    mysqli_query($conn, $query);
    mysqli_close($conn);
    return ["success"=>"false", "error"=>"Your program was unable to compile! Please check for syntax errors and resubmit. [Syntax error]"];
  }
  //runtime error
	else if ($runtime_error) {
    $conn = mysqli_connect(host, user, pw, db);
    $cur_time = date("Y-m-d H:i:s");
    $output_a = mysqli_real_escape_string($conn, $exec_data['output']);
    $query = "INSERT INTO submissions (id, team, problem, time, subid, code, output, success, error) VALUES (NULL, '$team', '$problem_number','$cur_time','$processname','$data','$output_a', 'No', 'Runtime')";

    mysqli_query($conn, $query);
    mysqli_close($conn);
    return ["success"=>"false", "error"=>"Your program encountered an error while running! Please check your program logic and resubmit. [Runtime error]"];
  }
  //something terrible occured
  return ["success"=>"false", "error"=>"Unknown error, something huge has gone wrong!"];
}
function proc_safety() {
  ini_set('memory_limit', 360);
  ini_set('max_input_time',360);
	$max_proc_count = exec("/bin/bash -c \"ulimit -u 2>&1\""); //stop abuse of compilation, limit processes
	$processes = array();
  ini_set('memory_limit', (ini_get('memory_limit')+1).'M');
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