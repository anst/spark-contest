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
$panel->route('/api/<string>', function($panel, $api_query) {
	if($api_query==="login") {

	} else if($api_query==="compile") {
		$processname = md5(time() . getmypid() .rand(1,10));
		$data = "public class HelloWorld{public static void main(String []args){System.out.println(\"Hello World\\n## ###\\n$$$\");}}";
		$inputs = "";
		$args = "";
		$data .= "\n";
		$data=preg_replace('/(\r\n|\r|\n)/s',"\n",$data);

		$max_proc_count = exec("ulimit -u"); //stop abuse of compilation, limit processes
		$processes = array();

		exec("ps -fhu andy| grep /tmp/| awk -F/ '{print $3}'", $processes); //change username to allow ps to work
		if(count($processes) > ($max_proc_count - 20)) {
		   echo "Pleasse try in a moment, server is overloaded.";
		   exit(0);
		}
		try{ //check if we've run out of memory, update the amount of memory needed to run
		  ini_set('memory_limit', (ini_get('memory_limit')+1).'M');
		} catch(Exception $e){
		    throw new Exception('Out of memory, try again.');
		}

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

		$sourcefile = "/tmp/$processname/$class" . ".java";
		$sourcedir  = "/tmp/$processname/";
		$classfile = "/tmp/$processname/$class" . ".class";
		@mkdir($sourcedir, 0755, true);
		$outputfile = "$classfile";
		$command = "javac -cp . {$sourcefile} 2>&1" ;
		chdir("/tmp/$processname");
		$handle = fopen($sourcefile, 'w+');
		fwrite($handle, $data); 
		fclose($handle);
		proc_exec($command, $inputs);
		$command = "java -classpath $sourcedir $class $args" ;
		proc_exec($command, $inputs);
		exec("rm -rf /tmp/$processname*");//remove directory subject to change
	}
	//echo returnApiMessage(["query"=>$api_query]);
});
$panel->run();

?>