<?php
define('EXTRACT_DIRECTORY', "var/composer.phar");

require_once ('vendor/autoload.php');
require_once (EXTRACT_DIRECTORY.'/vendor/autoload.php');


//Use the Composer classes 
use Composer\Console\Application; 
use Composer\Command\UpdateCommand; 
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

putenv('COMPOSER_HOME=' . __DIR__ . '/vendor/bin/composer');
//Create the commands 
$input = new ArrayInput(['command' => 'install','--no-dev' => true]);
// Setup composer output formatter
$stream = fopen('php://output', 'w+');
$output = new StreamOutput($stream);
//Create the application and run it with the commands
$application = new Application(); 
$application->setAutoExit(false);
$application->run($input,$output);
?>