<?php











namespace Composer\Repository;

use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Loader\LoaderInterface;




class ArtifactRepository extends ArrayRepository
{

protected $loader;

protected $lookup;

public function __construct(array $repoConfig, IOInterface $io)
{
if (!extension_loaded('zip')) {
throw new \RuntimeException('The artifact repository requires PHP\'s zip extension');
}

$this->loader = new ArrayLoader();
$this->lookup = $repoConfig['url'];
$this->io = $io;
}

protected function initialize()
{
parent::initialize();

$this->scanDirectory($this->lookup);
}

private function scanDirectory($path)
{
$io = $this->io;

$directory = new \RecursiveDirectoryIterator($path);
$iterator = new \RecursiveIteratorIterator($directory);
$regex = new \RegexIterator($iterator, '/^.+\.(zip|phar)$/i');
foreach ($regex as $file) {

if (!$file->isFile()) {
continue;
}

$package = $this->getComposerInformation($file);
if (!$package) {
if ($io->isVerbose()) {
$io->writeError("File <comment>{$file->getBasename()}</comment> doesn't seem to hold a package");
}
continue;
}

if ($io->isVerbose()) {
$template = 'Found package <info>%s</info> (<comment>%s</comment>) in file <info>%s</info>';
$io->writeError(sprintf($template, $package->getName(), $package->getPrettyVersion(), $file->getBasename()));
}

$this->addPackage($package);
}
}








private function locateFile(\ZipArchive $zip, $filename)
{
$indexOfShortestMatch = false;
$lengthOfShortestMatch = -1;

for ($i = 0; $i < $zip->numFiles; $i++) {
$stat = $zip->statIndex($i);
if (strcmp(basename($stat['name']), $filename) === 0) {
$directoryName = dirname($stat['name']);
if ($directoryName == '.') {

 
 return $i;
}

if (strpos($directoryName, '\\') !== false ||
strpos($directoryName, '/') !== false) {

 continue;
}

$length = strlen($stat['name']);
if ($indexOfShortestMatch == false || $length < $lengthOfShortestMatch) {

 $contents = $zip->getFromIndex($i);
if ($contents !== false) {
$indexOfShortestMatch = $i;
$lengthOfShortestMatch = $length;
}
}
}
}

return $indexOfShortestMatch;
}

private function getComposerInformation(\SplFileInfo $file)
{
$zip = new \ZipArchive();
$zip->open($file->getPathname());

if (0 == $zip->numFiles) {
return false;
}

$foundFileIndex = $this->locateFile($zip, 'composer.json');
if (false === $foundFileIndex) {
return false;
}

$configurationFileName = $zip->getNameIndex($foundFileIndex);

$composerFile = "zip://{$file->getPathname()}#$configurationFileName";
$json = file_get_contents($composerFile);

$package = JsonFile::parseJson($json, $composerFile);
$package['dist'] = array(
'type' => 'zip',
'url' => $file->getPathname(),
'shasum' => sha1_file($file->getRealPath())
);

$package = $this->loader->load($package);

return $package;
}
}
