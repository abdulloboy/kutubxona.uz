<?php










namespace Symfony\Component\Process;

use Symfony\Component\Process\Exception\RuntimeException;












class PhpProcess extends Process
{











public function __construct($script, $cwd = null, array $env = array(), $timeout = 60, array $options = array())
{
$executableFinder = new PhpExecutableFinder();
if (false === $php = $executableFinder->find()) {
$php = null;
}

parent::__construct($php, $cwd, $env, $script, $timeout, $options);
}






public function setPhpBinary($php)
{
$this->setCommandLine($php);
}




public function start($callback = null)
{
if (null === $this->getCommandLine()) {
throw new RuntimeException('Unable to find the PHP executable.');
}

parent::start($callback);
}
}
