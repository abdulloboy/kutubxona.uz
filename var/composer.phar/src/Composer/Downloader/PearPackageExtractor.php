<?php











namespace Composer\Downloader;

use Composer\Util\Filesystem;










class PearPackageExtractor
{
private static $rolesWithoutPackageNamePrefix = array('php', 'script', 'www');

private $filesystem;
private $file;

public function __construct($file)
{
if (!is_file($file)) {
throw new \UnexpectedValueException('PEAR package file is not found at '.$file);
}

$this->filesystem = new Filesystem();
$this->file = $file;
}











public function extractTo($target, array $roles = array('php' => '/', 'script' => '/bin'), $vars = array())
{
$extractionPath = $target.'/tarball';

try {
$archive = new \PharData($this->file);
$archive->extractTo($extractionPath, null, true);

if (!is_file($this->combine($extractionPath, '/package.xml'))) {
throw new \RuntimeException('Invalid PEAR package. It must contain package.xml file.');
}

$fileCopyActions = $this->buildCopyActions($extractionPath, $roles, $vars);
$this->copyFiles($fileCopyActions, $extractionPath, $target, $roles, $vars);
$this->filesystem->removeDirectory($extractionPath);
} catch (\Exception $exception) {
throw new \UnexpectedValueException(sprintf('Failed to extract PEAR package %s to %s. Reason: %s', $this->file, $target, $exception->getMessage()), 0, $exception);
}
}










private function copyFiles($files, $source, $target, $roles, $vars)
{
foreach ($files as $file) {
$from = $this->combine($source, $file['from']);
$to = $this->combine($target, $roles[$file['role']]);
$to = $this->combine($to, $file['to']);
$tasks = $file['tasks'];
$this->copyFile($from, $to, $tasks, $vars);
}
}

private function copyFile($from, $to, $tasks, $vars)
{
if (!is_file($from)) {
throw new \RuntimeException('Invalid PEAR package. package.xml defines file that is not located inside tarball.');
}

$this->filesystem->ensureDirectoryExists(dirname($to));

if (0 == count($tasks)) {
$copied = copy($from, $to);
} else {
$content = file_get_contents($from);
$replacements = array();
foreach ($tasks as $task) {
$pattern = $task['from'];
$varName = $task['to'];
if (isset($vars[$varName])) {
if ($varName === 'php_bin' && false === strpos($to, '.bat')) {
$replacements[$pattern] = preg_replace('{\.bat$}', '', $vars[$varName]);
} else {
$replacements[$pattern] = $vars[$varName];
}
}
}
$content = strtr($content, $replacements);

$copied = file_put_contents($to, $content);
}

if (false === $copied) {
throw new \RuntimeException(sprintf('Failed to copy %s to %s', $from, $to));
}
}











private function buildCopyActions($source, array $roles, $vars)
{

$package = simplexml_load_string(file_get_contents($this->combine($source, 'package.xml')));
if (false === $package) {
throw new \RuntimeException('Package definition file is not valid.');
}

$packageSchemaVersion = $package['version'];
if ('1.0' == $packageSchemaVersion) {
$children = $package->release->filelist->children();
$packageName = (string) $package->name;
$packageVersion = (string) $package->release->version;
$sourceDir = $packageName . '-' . $packageVersion;
$result = $this->buildSourceList10($children, $roles, $sourceDir, '', null, $packageName);
} elseif ('2.0' == $packageSchemaVersion || '2.1' == $packageSchemaVersion) {
$children = $package->contents->children();
$packageName = (string) $package->name;
$packageVersion = (string) $package->version->release;
$sourceDir = $packageName . '-' . $packageVersion;
$result = $this->buildSourceList20($children, $roles, $sourceDir, '', null, $packageName);

$namespaces = $package->getNamespaces();
$package->registerXPathNamespace('ns', $namespaces['']);
$releaseNodes = $package->xpath('ns:phprelease');
$this->applyRelease($result, $releaseNodes, $vars);
} else {
throw new \RuntimeException('Unsupported schema version of package definition file.');
}

return $result;
}

private function applyRelease(&$actions, $releaseNodes, $vars)
{
foreach ($releaseNodes as $releaseNode) {
$requiredOs = $releaseNode->installconditions && $releaseNode->installconditions->os && $releaseNode->installconditions->os->name ? (string) $releaseNode->installconditions->os->name : '';
if ($requiredOs && $vars['os'] != $requiredOs) {
continue;
}

if ($releaseNode->filelist) {
foreach ($releaseNode->filelist->children() as $action) {
if ('install' == $action->getName()) {
$name = (string) $action['name'];
$as = (string) $action['as'];
if (isset($actions[$name])) {
$actions[$name]['to'] = $as;
}
} elseif ('ignore' == $action->getName()) {
$name = (string) $action['name'];
unset($actions[$name]);
} else {

 }
}
}
break;
}
}

private function buildSourceList10($children, $targetRoles, $source, $target, $role, $packageName)
{
$result = array();


 foreach ($children as $child) {

if ($child->getName() == 'dir') {
$dirSource = $this->combine($source, (string) $child['name']);
$dirTarget = $child['baseinstalldir'] ?: $target;
$dirRole = $child['role'] ?: $role;
$dirFiles = $this->buildSourceList10($child->children(), $targetRoles, $dirSource, $dirTarget, $dirRole, $packageName);
$result = array_merge($result, $dirFiles);
} elseif ($child->getName() == 'file') {
$fileRole = (string) $child['role'] ?: $role;
if (isset($targetRoles[$fileRole])) {
$fileName = (string) ($child['name'] ?: $child[0]); 
 $fileSource = $this->combine($source, $fileName);
$fileTarget = $this->combine((string) $child['baseinstalldir'] ?: $target, $fileName);
if (!in_array($fileRole, self::$rolesWithoutPackageNamePrefix)) {
$fileTarget = $packageName . '/' . $fileTarget;
}
$result[(string) $child['name']] = array('from' => $fileSource, 'to' => $fileTarget, 'role' => $fileRole, 'tasks' => array());
}
}
}

return $result;
}

private function buildSourceList20($children, $targetRoles, $source, $target, $role, $packageName)
{
$result = array();


 foreach ($children as $child) {

if ('dir' == $child->getName()) {
$dirSource = $this->combine($source, $child['name']);
$dirTarget = $child['baseinstalldir'] ?: $target;
$dirRole = $child['role'] ?: $role;
$dirFiles = $this->buildSourceList20($child->children(), $targetRoles, $dirSource, $dirTarget, $dirRole, $packageName);
$result = array_merge($result, $dirFiles);
} elseif ('file' == $child->getName()) {
$fileRole = (string) $child['role'] ?: $role;
if (isset($targetRoles[$fileRole])) {
$fileSource = $this->combine($source, (string) $child['name']);
$fileTarget = $this->combine((string) ($child['baseinstalldir'] ?: $target), (string) $child['name']);
$fileTasks = array();
foreach ($child->children('http://pear.php.net/dtd/tasks-1.0') as $taskNode) {
if ('replace' == $taskNode->getName()) {
$fileTasks[] = array('from' => (string) $taskNode->attributes()->from, 'to' => (string) $taskNode->attributes()->to);
}
}
if (!in_array($fileRole, self::$rolesWithoutPackageNamePrefix)) {
$fileTarget = $packageName . '/' . $fileTarget;
}
$result[(string) $child['name']] = array('from' => $fileSource, 'to' => $fileTarget, 'role' => $fileRole, 'tasks' => $fileTasks);
}
}
}

return $result;
}

private function combine($left, $right)
{
return rtrim($left, '/') . '/' . ltrim($right, '/');
}
}
