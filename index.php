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
$panel->route('/compile/', function($panel) {
	return $panel->render("compile_temp.html",[
		"title"=>title,
		"contest_name"=>contest_name,
	]);
}, array('GET'));
$panel->route('/api/<string>', function($panel, $api_query) {
	#header('Content-Type: application/json'); //we're returning JSON data
	if($api_query==="login") {

	}
	else if($api_query==="compile") {
		$processname = md5(time() . getmypid() .rand(1,10)); //change this to md5(TeamNumber+Timestamp+rand(1,10000))
		if(!isset($_POST['code'])) return returnApiMessage(["success"=>"false","error"=>"Invalid API Query."]);
		$data = $_POST['code'];
		#$data = "import java.io.*;public class untitled {    public static void main(String[] args) throws Exception{        System.out.println(new yo().lel()+new bro().lal());        Thread.sleep(15);              System.out.println(new yo().lel()+new bro().lal());                    }    static class yo {        static String lel() {return \"HEY \";}    }}class bro {    static String lal() {return \"YA\";}}class Jonathan240Exception extends Exception {    public Jonathan240Exception() {            }    public String toString() {        return \"OH NO JONATHAN 240\";    }}";
		$inputs = "";
		$args = "";
		$data = preg_replace('/(\r\n|\r|\n)/s',"\n",$data);

		$proccess_safety = proc_safety();
		if($proccess_safety!=="Success") return returnApiMessage(["error"=>$proccess_safety]);

		#register_shutdown_function('shutdown'); //before call to exit(), execute shutdown()
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
		$compiled = ($classerror||$packageerror)?FALSE:compileProgram("/tmp/$processname/$class" . ".java", "/tmp/$processname/", "/tmp/$processname/$class" . ".class", $class, $inputs, $args, $processname, $data);
		if($classerror) {
			echo returnApiMessage([
					"success"=>"false",
					"error"=>"Error - At least one public class is required"
				]
			);
		} else if ($packageerror) {
			echo returnApiMessage([
					"success"=>"false",
					"error"=>"Error - Packages are not allowed"
				]
			);
		}
		else echo returnApiMessage($compiled);
		//if($compiled['exec']["output"]=="Hello World\n## ###\n")
		//	echo "YUUUUS";
	}
	//echo returnApiMessage(["query"=>$api_query]);
},array('GET','POST'));
$panel->run();

?>