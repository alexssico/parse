<?php
require_once "config.php";
require_once "controller/parse.php";
require_once 'libs/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('template/tpl');
$parse = new ParseClass();
$parse->html= new Twig_Environment($loader);

try{
	$parse->db = new PDO("mysql:host=".HOST.";dbname=".DB_NAME."", USER, PASSWORD, array(
		 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));	
} catch (PDOException $e){
	print "Error!: " . $e->getMessage() . "<br/>";
 	die();
}

switch ($_GET['task']) {
	case 'parse':
		$parse->parse();
	break;
	
	default:
		$parse->show();	
	break;
 }
?>

