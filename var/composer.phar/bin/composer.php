<?php

if (PHP_SAPI !== 'cli') {
   // echo 'Warning: Composer should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

require __DIR__.'/../src/bootstrap.php';

use Composer\Console\Application; 
use Composer\Command\UpdateCommand; 
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

error_reporting(-1);

if (function_exists('ini_set')) {
    @ini_set('display_errors', 1);

    $memoryInBytes = function ($value) {
        $unit = strtolower(substr($value, -1, 1));
        $value = (int) $value;
        switch($unit) {
            case 'g':
                $value *= 1024;
                // no break (cumulative multiplier)
            case 'm':
                $value *= 1024;
                // no break (cumulative multiplier)
            case 'k':
                $value *= 1024;
        }

        return $value;
    };

    $memoryLimit = trim(ini_get('memory_limit'));
    // Increase memory_limit if it is lower than 1GB
    if ($memoryLimit != -1 && $memoryInBytes($memoryLimit) < 1024 * 1024 * 1024) {
        @ini_set('memory_limit', '1G');
    }
    unset($memoryInBytes, $memoryLimit);
}

chdir('../../../');
$_SERVER['argc']=3;
$_SERVER['argv'][]='-';
$_SERVER['argv'][]='install';
$_SERVER['argv'][]='--prefer-dist';
// run the command application


$input = new ArrayInput(['command' => 'install','--no-dev' => true]);
// Setup composer output formatter
$stream = fopen('php://output', 'w+');
$output = new StreamOutput($stream);
//Create the application and run it with the commands
$application = new Application(); 
$application->setAutoExit(false);
$application = new Application();
$application->run(null,$output);