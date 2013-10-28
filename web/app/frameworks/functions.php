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


?>