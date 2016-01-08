<?php
/**********defines***********/
define("PATH_TO_DIR", dirname(__FILE__));
define("BASE_URL", 'http://'.$_SERVER['HTTP_HOST'].'/');
if(!session_id())
	session_start();


ini_set("display_errors",1);
error_reporting(E_ALL);
require_once(PATH_TO_DIR.'/core/system.php');
require_once(PATH_TO_DIR.'/templates/header.php');
require_once(PATH_TO_DIR.'/templates/content.php');
require_once(PATH_TO_DIR.'/templates/footer.php');