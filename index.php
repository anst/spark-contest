<?php
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org)
*/
require_once dirname(__FILE__).'/app/lib/Phpass.php';
require_once dirname(__FILE__).'/app/config/config.php';
require_once dirname(__FILE__).'/app/frameworks/panel.php';
require_once dirname(__FILE__).'/app/functions/functions.php';

session_start();
session_regenerate_id();

$panel = new Panel('panel',false, 'logs/' . date('Y-m-d') . '.txt'); //include default routing engine with logs enabled

$panel->route('/', function($panel) { //index router, check for login
	$string = file_get_contents(dirname(__FILE__)."/app/config/schools.json");
	$json_a = json_decode($string, true);
	if(!isLoggedIn()) return $panel->render("login.html",[
		"title"=>title,
		"contest_name"=>contest_name,
		"schools"=>$json_a,
	]);
	return $panel->render("home.html",[
		"title"=>title,
		"contest_name"=>contest_name,
		"t"=>$_SESSION['team'],
	]);
});
$panel->route('/submit', function($panel) {
	http_response_code(200);
	return $panel->render("submit.html",[
		"title"=>title,
		"contest_name"=>contest_name,
	]);
});
$panel->route('/logout', function($panel) {
	http_response_code(200);
	logout();
});
$panel->route('/compile', function($panel) {
	http_response_code(200);
	return $panel->render("compile_temp.html",[
		"title"=>title,
		"contest_name"=>contest_name,
	]);
});
$panel->route('/temp', function($panel) {
	http_response_code(200);
	return $panel->render("clar.html",[
		"title"=>title,
		"contest_name"=>contest_name,
	]);
});
$panel->route('/api/<string>/<string>', function($panel, $api_query, $type) {
	http_response_code(200);

	if($api_query==="user") {
		if($type==="team") {
			return getTeamNumber();
		}
		else if($type === "written") {
			return getWrittenScores();
		}
	}
});
$panel->route('/api/<string>', function($panel, $api_query) {
	#header('Content-Type: application/json'); //we're returning JSON data
	http_response_code(200);
	if($api_query==="register") {
		extract($_POST);
		if(is_numeric($team)&&strlen($team)<=3&&strlen($password)<=64&&strlen($password)>=6&&$teamselect!=="null"&&$division==="Advanced"||$division==="Novice"&&$school!=="null") {
			if($teamselect=="1") {
				if(preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member1))
					echo register($team,$password,$school,$division,['member1'=>$member1,'member2'=>$member2===""?NULL:$member2,'member3'=>$member3===""?NULL:$member3]);
				else echo returnApiMessage(['error'=>'Don\'t mess with JS input validation. We\'re smarter than that.']);
			} else if ($teamselect=="2") {
				if (preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member1)&&preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member2))
					echo register($team,$password,$school,$division,['member1'=>$member1,'member2'=>$member2===""?NULL:$member2,'member3'=>$member3===""?NULL:$member3]);
				else echo returnApiMessage(['error'=>'Don\'t mess with JS input validation. We\'re smarter than that.']);
			} else {
				if (preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member1)&&preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member2)&&preg_match("/^[a-zA-Z]+\s+([-a-zA-Z.'\s]|[0-9](nd|rd|th))+$/", $member3))
					echo register($team,$password,$school,$division,['member1'=>$member1,'member2'=>$member2===""?NULL:$member2,'member3'=>$member3===""?NULL:$member3]);
				else echo returnApiMessage(['error'=>'Don\'t mess with JS input validation. We\'re smarter than that.']);
			}
		}
		else {
			echo returnApiMessage(['error'=>'Don\'t mess with JS input validation. We\'re smarter than that.']);
		}
	} 
	else if($api_query==="login") {
		extract($_POST);
		if(is_numeric($team)&&strlen($team)<=3&&strlen($password)<=64&&strlen($password)>=6) {
			echo returnApiMessage(login($team, $password));
		}
		else {
			echo returnApiMessage(['error'=>'Don\'t mess with JS input validation. We\'re smarter than that.']);
		}
	}
	else if($api_query==="scoreboard") {
		return getScoreboard();
	}
	else if($api_query==="compile") {
		$processname = md5(time() . getmypid() .rand(1,10)); //change this to md5(TeamNumber+Timestamp+rand(1,10000))
		#if(!isset($_POST['code'])) return returnApiMessage(["success"=>"false","error"=>"Invalid API Query."]);
		#$data = $_POST['code'];
		$data = "import java.io.*;public class untitled {    public static void main(String[] args) throws Exception{        System.out.println(new yo().lel()+new bro().lal());        Thread.sleep(15);              System.out.println(new yo().lel()+new bro().lal());                    }    static class yo {        static String lel() {return \"HEY \";}    }}class bro {    static String lal() {return \"YA\";}}class Jonathan240Exception extends Exception {    public Jonathan240Exception() {            }    public String toString() {        return \"OH NO JONATHAN 240\";    }}";
		$inputs = "";
		$args = "";
		$data = preg_replace('/(\r\n|\r|\n)/s',"\n",$data);

		$proccess_safety = proc_safety();
		if($proccess_safety!=="Success") return returnApiMessage(["error"=>$proccess_safety]);

		register_shutdown_function('shutdown'); //before call to exit(), execute shutdown()
		$source = preg_split("/(\n|;)/",$data);
		$package = "";
		$class = "";
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
		}
		$classerror = false;
		$packageerror = false;
		if(!strlen($class)) $classerror = true;
		if(strlen($package)) $packageerror = true;
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
		}
		$compiled = ($classerror||$packageerror)?FALSE:compileProgram("/tmp/$processname/$class" . ".java", "/tmp/$processname/", "/tmp/$processname/$class" . ".class", $class, $inputs, $args, $processname, $data);
		echo returnApiMessage($compiled);
		//if($compiled['exec']["output"]=="Hello World\n## ###\n")
		//	echo "YUUUUS";
	}
	//echo returnApiMessage(["query"=>$api_query]);
});
$panel->run();

?>