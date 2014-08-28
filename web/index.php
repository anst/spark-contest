<?php
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org)
*/

require_once dirname(__FILE__).'/app/config/config.php';
require_once dirname(__FILE__).'/app/frameworks/panel.php';
require_once dirname(__FILE__).'/app/functions/functions.php';

session_start();
session_regenerate_id();
date_default_timezone_set('America/Chicago');

$panel = new Panel('panel',false, 'logs/' . date('Y-m-d') . '.txt'); //include default routing engine with logs enabled

$panel->route('/', function($panel) { //index router, check for login
	$schools = json_decode(file_get_contents(dirname(__FILE__)."/app/config/schools.json"), true);
	if(!isLoggedIn()) return $panel->render("login.html",[
		"title"=>title,
		"contest_name"=>contest_name,
		"schools"=>$schools,
	]);
	return $panel->render("home.html",[
		"title"=>title,
		"contest_name"=>contest_name,
		"t"=>$_SESSION['team'],
		"navbar_title"=>navbar_title,
		"written"=>getTeamWritten(),
		"info"=>getTeamInfo(),
		"pizza_ordered"=>hasOrderedPizza($_SESSION['team']),
	]);
});
$panel->route('/scoreboard', function($panel) {
	$schools = json_decode(file_get_contents(dirname(__FILE__)."/app/config/schools.json"), true);
	if(!isLoggedIn()) return $panel->render("login.html",[
		"title"=>title,
		"contest_name"=>contest_name,
		"schools"=>$schools,
	]);
	return $panel->render("scoreboard.html",[
		"title"=>title,
		"contest_name"=>contest_name,
	]);
});
$panel->route('/admin', function($panel) {
	if(adminIsLoggedIn()) return $panel->render("admin.html",[]);
	return $panel->render("adminlogin.html",[]);
});
$panel->route('/admin/login', function($panel) {
	if(md5($_POST["code"])===global_admin_key) {
		startAdminSession();
		return header("Location: /admin");
	} else {
		die("Don't hack, it's not good for your health.");
	}
});
$panel->route('/submit', function($panel) {
	$schools = json_decode(file_get_contents(dirname(__FILE__)."/app/config/schools.json"), true);
	$problems = json_decode(file_get_contents(dirname(dirname(__FILE__))."/server/problems/problems.json"), true);
	$parsed = [];
	foreach ($problems as $problem=>$a) {
		$parsed[$problem]=[$problem=>$a["title"], "points"=>$a["info"]["points"], "timeout"=>$a["info"]["points"]];
	}
	if(!isLoggedIn()) return $panel->render("login.html",[
		"title"=>title,
		"contest_name"=>contest_name,
		"schools"=>$schools,
	]);
	return $panel->render("submit.html",[
		"title"=>title,
		"contest_name"=>contest_name,
		"navbar_title"=>navbar_title,
		"problems"=>$parsed,
	]);
});
$panel->route('/logout', function($panel) {
	logout();
});
$panel->route('/api/<string>/<string>', function($panel, $api_query, $type) {
	
	if($api_query==="time") {
		if($api_query==="increment"&&md5($_POST['key'])===global_admin_key) {
			incrementTime($_POST['time']);
		} else if($api_query==="start"/*&&md5($_POST['key'])===global_admin_key*/) {
			startTime();
		} else if($api_query==="pause"&&md5($_POST['key'])===global_admin_key) {
			pauseTime();
		}
	}
	if($api_query==="user") {
		if(isLoggedIn()) {
			if($type==="team") {
				return returnApiMessage(getTeamNumber());
			}
			else if($type === "written") {
				return getWrittenScores();
			}
		} else echo returnApiMessage(["team"=>"null"]);
	} 
});
$panel->route('/api/<string>', function($panel, $api_query) {
	#header('Content-Type: application/json'); //we're returning JSON data
	if($api_query==="register") {
		extract($_POST);
		if(is_numeric($team)&&strlen($team)<=3&&strlen($password)<=64&&strlen($password)>=6&&$teamselect!=="null"&&$division==="Advanced"||$division==="Novice"&&$school!=="null") {
			if($teamselect=="1") {
				if(preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member1))
					echo register($team,$password,$school,$division,['member1'=>$member1,'member2'=>$member2===""?NULL:$member2,'member3'=>$member3===""?NULL:$member3]);
				else echo returnApiMessage(['error'=>'Please follow correct input guidelines.']);
			} else if ($teamselect=="2") {
				if (preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member1)&&preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member2))
					echo register($team,$password,$school,$division,['member1'=>$member1,'member2'=>$member2===""?NULL:$member2,'member3'=>$member3===""?NULL:$member3]);
				else echo returnApiMessage(['error'=>'Please follow correct input guidelines.']);
			} else {
				if (preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member1)&&preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member2)&&preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member3))
					echo register($team,$password,$school,$division,['member1'=>$member1,'member2'=>$member2===""?NULL:$member2,'member3'=>$member3===""?NULL:$member3]);
				else echo returnApiMessage(['error'=>'Please follow correct input guidelines.']);
			}
		} 
		else {
			echo returnApiMessage(['error'=>'Please follow correct input guidelines.']);
		}
	} 
	else if($api_query==="login") {
		extract($_POST);
		if(is_numeric($team)&&strlen($team)<=3&&strlen($password)<=64&&strlen($password)>=6) {
			echo returnApiMessage(login($team, $password));
		}
		else {
			echo returnApiMessage(['error'=>'Please follow correct input guidelines.']);
		}
	}
	else if($api_query==="problems") {
		$problems = json_decode(file_get_contents(dirname(__FILE__)."/app/problems/problems.json"), true);
		$parsed = [];
		foreach ($problems as $problem=>$a) {
			$parsed[$problem]=[$problem=>$a["title"], "points"=>$a["info"]["points"], "timeout"=>$a["info"]["points"]];
		}
		return returnApiMessage($parsed);
	}
	else if($api_query==="pizza") {
		extract($_POST);
		return returnApiMessage(orderPizza($_SESSION['team'],['pepe'=>$pepperoni,'ches'=>$cheese,'saus'=>$sausage]));
	}
	else if($api_query==="compile") {
		if(isLoggedIn()) {
			$processname = strtoupper(substr(md5(getTeamNumber()['team'].time().rand(1,10000)), 26));
			if(!isset($_POST['code'])) return returnApiMessage(["success"=>"false","error"=>"Invalid API Query."]);
			$data = $_POST['code'];
			$inputs = "";
			$args = "";
			$data = preg_replace('/(\r\n|\r|\n)/s',"\n",$data);
			$problem_number = $_POST['problem'];
			$proccess_safety = proc_safety();
			if($proccess_safety!=="Success") return returnApiMessage(["error"=>$proccess_safety]);

			register_shutdown_function('shutdown'); //before call to exit(), execute shutdown()
			$source = preg_split("/(\n|;)/",$data);
			$package = "";
			$class = "";
			$hack = false;
			foreach($source as $line){
				$line = trim($line);
				$pattern = "/^package\s+(.*)/";
				if(preg_match($pattern, $line, $matches )){
					$package = preg_replace('/\./', "/", $matches[1]);
				}
				$pattern = "/^public(\s+)class(\s+)(\w+).*/";
				if(preg_match($pattern, $line, $matches )){
					$class = trim($matches[3]);
				}
				$pattern = "/^(exec)/";
				if(preg_match($pattern, $line, $matches )){
					$hack = trim($matches[1]);
				}
			}
			$classerror = false;
			$packageerror = false;
			if(!strlen($class)) $classerror = true;
			if(strlen($package)) $packageerror = true;
			if($hack) {
				echo returnApiMessage([
						"success"=>"false",
						"error"=>"exec() not allowed! You have been reported."
					]
				);
			}
			if($classerror) {
				echo returnApiMessage([
						"success"=>"false",
						"error"=>"At least one public class is required"
					]
				);
			} else if ($packageerror) {
				echo returnApiMessage([
						"success"=>"false",
						"error"=>"Packages are not allowed"
					]
				);
			} else {
				$send = [
					"team"=> getTeamNumber()['team'],
					"class" => $class,
					"inputs" => $inputs,
					"args" => $args,
					"processname" => $processname,
					"data" => $data,
					"problem_number" => $problem_number
				];
				$fp = stream_socket_client("tcp://localhost:1337", $errno, $errorMessage);
				fwrite($fp, json_encode($send));
				fclose($fp);
				echo returnApiMessage(["success"=>"true"]);
			}
		} else {
			echo returnApiMessage(["error"=>"You aren't logged in!"]);
		}
	}
});
$panel->run();

?>