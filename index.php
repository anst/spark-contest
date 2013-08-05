<?php
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org)
*/
require_once dirname(__FILE__).'/app/lib/mysql.php';
require_once dirname(__FILE__).'/app/config/config.php';
require_once dirname(__FILE__).'/app/frameworks/panel.php';
require_once dirname(__FILE__).'/app/functions/functions.php';

$panel = new Panel('panel',false, 'logs/' . date('Y-m-d') . '.txt'); //include default routing engine with logs enabled

$panel->route('/', function($panel) { //index router, check for login
	if(!isLoggedIn()) return $panel->render("login.html",[
		"title"=>title,
		"contest_name"=>contest_name,
	]);
	return $panel->render("home.html",[
		"title"=>title,
		"contest_name"=>contest_name,
	]);
});
$panel->route('/compile', function($panel) {
	return $panel->render("compile_temp.html",[
		"title"=>title,
		"contest_name"=>contest_name,
	]);
});
$panel->route('/api/<string>', function($panel, $api_query) {
	#header('Content-Type: application/json'); //we're returning JSON data
	if($api_query==="login") {

	}
	else if($api_query==="compile") {
		$processname = md5(time() . getmypid() .rand(1,10)); //change this to md5(TeamNumber+Timestamp+rand(1,10000))
		$data = $_POST['code'];
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

		if(!strlen($class) ){
			echo "Error - At least one public class is required";
			exit(0);
		}
		else if(strlen($package)) {
			echo "Error - Packages are not allowed";
			exit(0);
		}
		$compiled = compileProgram("/tmp/$processname/$class" . ".java", "/tmp/$processname/", "/tmp/$processname/$class" . ".class", $class, $inputs, $args, $processname, $data);
		echo returnApiMessage([
				"success"=>$compiled
			]
		);
		//if($compiled['exec']["output"]=="Hello World\n## ###\n")
		//	echo "YUUUUS";
	}
	//echo returnApiMessage(["query"=>$api_query]);
});
$panel->run();

?>