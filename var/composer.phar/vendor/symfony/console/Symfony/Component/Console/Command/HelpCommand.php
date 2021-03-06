<?php










namespace Symfony\Component\Console\Command;

use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;






class HelpCommand extends Command
{
private $command;




protected function configure()
{
$this->ignoreValidationErrors();

$this
->setName('help')
->setDefinition(array(
new InputArgument('command_name', InputArgument::OPTIONAL, 'The command name', 'help'),
new InputOption('xml', null, InputOption::VALUE_NONE, 'To output help as XML'),
new InputOption('format', null, InputOption::VALUE_REQUIRED, 'To output help in other formats', 'txt'),
new InputOption('raw', null, InputOption::VALUE_NONE, 'To output raw command help'),
))
->setDescription('Displays help for a command')
->setHelp(<<<EOF
The <info>%command.name%</info> command displays help for a given command:

  <info>php %command.full_name% list</info>

You can also output the help in other formats by using the <comment>--format</comment> option:

  <info>php %command.full_name% --format=xml list</info>

To display the list of available commands, please use the <info>list</info> command.
EOF
)
;
}






public function setCommand(Command $command)
{
$this->command = $command;
}




protected function execute(InputInterface $input, OutputInterface $output)
{
if (null === $this->command) {
$this->command = $this->getApplication()->find($input->getArgument('command_name'));
}

if ($input->getOption('xml')) {
$input->setOption('format', 'xml');
}

$helper = new DescriptorHelper();
$helper->describe($output, $this->command, array(
'format' => $input->getOption('format'),
'raw_text' => $input->getOption('raw'),
));

$this->command = null;
}
}
