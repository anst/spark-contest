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

$panel->run();

?>