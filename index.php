<?php

ini_set('display_errors', true);

define('PATH', dirname(__FILE__));

require_once PATH . '/Migrator.php';

// 
// Handle posted data
// 
$error = null;
$userHtml = '';
$convertedHtml = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') try {

	// grab the user's post'd html
	$userHtml = isset($_POST['html']) ? $_POST['html'] : null;
	if ( ! $userHtml) {
		throw new Exception('Please enter some html');
	}

	// convert it
	$convertedHtml = Migrator::convert($userHtml);

} catch (Exception $e) {
	$error = $e->getMessage();
}

// 
// View time
// 

// view function to html escape
function escape($string) {
	return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
}

$view = (object) [
	'error' => $error,
	'userHtml' => $userHtml,
	'convertedHtml' => $convertedHtml,
];
include PATH . '/views/index.php';
