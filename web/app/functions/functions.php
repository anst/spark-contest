<?php
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org)
*/
function isLoggedIn() {
	return isset($_SESSION['team'])&&is_numeric($_SESSION['team']);
}
function adminIsLoggedIn() {
  return isset($_SESSION['admin'])&&$_SESSION['admin']===global_admin_key;
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

  $query = "INSERT IGNORE INTO scoreboard (team,score) VALUES ('$team','0');";
  $result = mysqli_query($conn, $query);
  $query = "SELECT password FROM teams WHERE team = '$team';";
  $result = mysqli_query($conn, $query);
  if(mysqli_num_rows($result)===0) {
    foreach ($members as &$member) {
      if($member!=="") {
        $member = mysqli_real_escape_string($conn, $member);
        $querys = "INSERT INTO written (name, team, score) VALUES ('$member', '$team','0')";
        mysqli_query($conn, $querys);
      }
    }
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
function getTeamWritten() {
  $team = $_SESSION['team'];
  $i = 0;
  $a = [];
  $link = mysqli_connect(host, user, pw, db);
  $result = mysqli_query($link, "SELECT * FROM written WHERE team = '$team';");
  while ($row = mysqli_fetch_assoc($result)) {
    $a[$i]['name'] = $row['name'];
    $a[$i]['id'] = $row['id'];
    $a[$i]['score'] = $row['score'];
    $i++;
  }
  return $a;
}
function generateRandomString($length = 6) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
function getTeamInfo() {
  $team = $_SESSION['team'];
  return mysqli_fetch_array(mysqli_query(mysqli_connect(host, user, pw, db), "SELECT * FROM teams WHERE team = '$team';"), MYSQL_ASSOC);
}
function hasOrderedPizza($team) {
  $link = mysqli_connect(host, user, pw, db);
  $result = mysqli_query($link, "SELECT * FROM pizza WHERE team = '$team';");
  return mysqli_num_rows($result)===0?false:mysqli_fetch_array($result)['ticket'];
}
function orderPizza($team, $pizza) {
  $link = mysqli_connect(host, user, pw, db);
  $result = mysqli_query($link, "SELECT * FROM pizza WHERE team = '$team';");
  extract($pizza);
  foreach ($pizza as &$p) {
      $p = mysqli_real_escape_string($link, $p);
  }
  $cost = ($pepe + $ches + $saus)*11;
  if(mysqli_num_rows($result)===0) {
    if(($pepe + $ches + $saus)*11>55) return ["error"=>"Maximum Pizza Order is $55!"];
    $ticket = generateRandomString();
    $result = mysqli_query($link, "INSERT INTO pizza (`team`, `cheese`, `pepperoni`, `sausage`, `cost`,`ticket`) VALUES ('$team','$ches','$pepe','$saus','$cost','$ticket');");
    return ["success"=>"You've placed your order. Go pay for your pizza with your Order ID: ".$ticket."!"];
  }
  return ["error"=>"You've already ordered!"];
}
function logout() {
  session_destroy();
  header("Location: /");
}
function startAdminSession() {
  setcookie("admin", global_admin_key);
  $_SESSION['admin'] = global_admin_key;
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
function shutdown() { //terminate program, print the errors
   $last_error = error_get_last();
   if( $last_error != NULL ){
      print_r ($last_error);
      echo 'Unknown exception, please try again.';
   }
}
?>