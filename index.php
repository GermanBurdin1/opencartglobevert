<?php
// Version
define('VERSION', '4.0.2.3');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit();
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

//Сomposer
require_once(DIR_SYSTEM . '../vendor/autoload.php');

// Framework
require_once(DIR_SYSTEM . 'framework.php');
