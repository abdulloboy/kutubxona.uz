<?php











namespace Composer\Command;

use Composer\Script\CommandEvent;
use Composer\Script\ScriptEvents;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;




class RunScriptCommand extends Command
{



protected $scriptEvents = array(
ScriptEvents::PRE_INSTALL_CMD,
ScriptEvents::POST_INSTALL_CMD,
ScriptEvents::PRE_UPDATE_CMD,
ScriptEvents::POST_UPDATE_CMD,
ScriptEvents::PRE_STATUS_CMD,
ScriptEvents::POST_STATUS_CMD,
ScriptEvents::POST_ROOT_PACKAGE_INSTALL,
ScriptEvents::POST_CREATE_PROJECT_CMD,
ScriptEvents::PRE_ARCHIVE_CMD,
ScriptEvents::POST_ARCHIVE_CMD,
ScriptEvents::PRE_AUTOLOAD_DUMP,
ScriptEvents::POST_AUTOLOAD_DUMP,
);

protected function configure()
{
$this
->setName('run-script')
->setDescription('Run the scripts defined in composer.json.')
->setDefinition(array(
new InputArgument('script', InputArgument::OPTIONAL, 'Script name to run.'),
new InputArgument('args', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, ''),
new InputOption('dev', null, InputOption::VALUE_NONE, 'Sets the dev mode.'),
new InputOption('no-dev', null, InputOption::VALUE_NONE, 'Disables the dev mode.'),
new InputOption('list', 'l', InputOption::VALUE_NONE, 'List scripts.'),
))
->setHelp(<<<EOT
The <info>run-script</info> command runs scripts defined in composer.json:

<info>php composer.phar run-script post-update-cmd</info>
EOT
)
;
}

protected function execute(InputInterface $input, OutputInterface $output)
{
if ($input->getOption('list')) {
return $this->listScripts();
} elseif (!$input->getArgument('script')) {
throw new \RunTimeException('Missing required argument "script"');
}

$script = $input->getArgument('script');
if (!in_array($script, $this->scriptEvents)) {
if (defined('Composer\Script\ScriptEvents::'.str_replace('-', '_', strtoupper($script)))) {
throw new \InvalidArgumentException(sprintf('Script "%s" cannot be run with this command', $script));
}
}

$composer = $this->getComposer();
$hasListeners = $composer->getEventDispatcher()->hasEventListeners(new CommandEvent($script, $composer, $this->getIO()));
if (!$hasListeners) {
throw new \InvalidArgumentException(sprintf('Script "%s" is not defined in this package', $script));
}


 $binDir = $composer->getConfig()->get('bin-dir');
if (is_dir($binDir)) {
$_SERVER['PATH'] = realpath($binDir).PATH_SEPARATOR.getenv('PATH');
putenv('PATH='.$_SERVER['PATH']);
}

$args = $input->getArgument('args');

return $composer->getEventDispatcher()->dispatchScript($script, $input->getOption('dev') || !$input->getOption('no-dev'), $args);
}

protected function listScripts()
{
$scripts = $this->getComposer()->getPackage()->getScripts();

if (!count($scripts)) {
return 0;
}

$this->getIO()->writeError('<info>scripts:</info>');
foreach ($scripts as $name => $script) {
$this->getIO()->write('  ' . $name);
}

return 0;
}
}
