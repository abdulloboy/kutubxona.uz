<?php











namespace Composer\Package\Loader;

use Composer\Package\BasePackage;
use Composer\Package\AliasPackage;
use Composer\Config;
use Composer\Factory;
use Composer\Package\Version\VersionParser;
use Composer\Repository\RepositoryManager;
use Composer\Repository\Vcs\HgDriver;
use Composer\IO\NullIO;
use Composer\Util\ProcessExecutor;
use Composer\Util\Git as GitUtil;
use Composer\Util\Svn as SvnUtil;








class RootPackageLoader extends ArrayLoader
{
private $manager;
private $config;
private $process;

public function __construct(RepositoryManager $manager, Config $config, VersionParser $parser = null, ProcessExecutor $process = null)
{
$this->manager = $manager;
$this->config = $config;
$this->process = $process ?: new ProcessExecutor();
parent::__construct($parser);
}

public function load(array $config, $class = 'Composer\Package\RootPackage')
{
if (!isset($config['name'])) {
$config['name'] = '__root__';
}
$autoVersioned = false;
if (!isset($config['version'])) {

 if (getenv('COMPOSER_ROOT_VERSION')) {
$version = getenv('COMPOSER_ROOT_VERSION');
} else {
$version = $this->guessVersion($config);
}

if (!$version) {
$version = '1.0.0';
$autoVersioned = true;
}

$config['version'] = $version;
}

$realPackage = $package = parent::load($config, $class);
if ($realPackage instanceof AliasPackage) {
$realPackage = $package->getAliasOf();
}

if ($autoVersioned) {
$realPackage->replaceVersion($realPackage->getVersion(), 'No version set (parsed as 1.0.0)');
}

if (isset($config['minimum-stability'])) {
$realPackage->setMinimumStability(VersionParser::normalizeStability($config['minimum-stability']));
}

$aliases = array();
$stabilityFlags = array();
$references = array();
foreach (array('require', 'require-dev') as $linkType) {
if (isset($config[$linkType])) {
$linkInfo = BasePackage::$supportedLinkTypes[$linkType];
$method = 'get'.ucfirst($linkInfo['method']);
$links = array();
foreach ($realPackage->$method() as $link) {
$links[$link->getTarget()] = $link->getConstraint()->getPrettyString();
}
$aliases = $this->extractAliases($links, $aliases);
$stabilityFlags = $this->extractStabilityFlags($links, $stabilityFlags, $realPackage->getMinimumStability());
$references = $this->extractReferences($links, $references);
}
}

$realPackage->setAliases($aliases);
$realPackage->setStabilityFlags($stabilityFlags);
$realPackage->setReferences($references);

if (isset($config['prefer-stable'])) {
$realPackage->setPreferStable((bool) $config['prefer-stable']);
}

$repos = Factory::createDefaultRepositories(null, $this->config, $this->manager);
foreach ($repos as $repo) {
$this->manager->addRepository($repo);
}
$realPackage->setRepositories($this->config->getRepositories());

return $package;
}

private function extractAliases(array $requires, array $aliases)
{
foreach ($requires as $reqName => $reqVersion) {
if (preg_match('{^([^,\s#]+)(?:#[^ ]+)? +as +([^,\s]+)$}', $reqVersion, $match)) {
$aliases[] = array(
'package' => strtolower($reqName),
'version' => $this->versionParser->normalize($match[1], $reqVersion),
'alias' => $match[2],
'alias_normalized' => $this->versionParser->normalize($match[2], $reqVersion),
);
}
}

return $aliases;
}

private function extractStabilityFlags(array $requires, array $stabilityFlags, $minimumStability)
{
$stabilities = BasePackage::$stabilities;
$minimumStability = $stabilities[$minimumStability];
foreach ($requires as $reqName => $reqVersion) {

 if (preg_match('{^[^@]*?@('.implode('|', array_keys($stabilities)).')$}i', $reqVersion, $match)) {
$name = strtolower($reqName);
$stability = $stabilities[VersionParser::normalizeStability($match[1])];

if (isset($stabilityFlags[$name]) && $stabilityFlags[$name] > $stability) {
continue;
}
$stabilityFlags[$name] = $stability;

continue;
}


 
 $reqVersion = preg_replace('{^([^,\s@]+) as .+$}', '$1', $reqVersion);
if (preg_match('{^[^,\s@]+$}', $reqVersion) && 'stable' !== ($stabilityName = VersionParser::parseStability($reqVersion))) {
$name = strtolower($reqName);
$stability = $stabilities[$stabilityName];
if ((isset($stabilityFlags[$name]) && $stabilityFlags[$name] > $stability) || ($minimumStability > $stability)) {
continue;
}
$stabilityFlags[$name] = $stability;
}
}

return $stabilityFlags;
}

private function extractReferences(array $requires, array $references)
{
foreach ($requires as $reqName => $reqVersion) {
$reqVersion = preg_replace('{^([^,\s@]+) as .+$}', '$1', $reqVersion);
if (preg_match('{^[^,\s@]+?#([a-f0-9]+)$}', $reqVersion, $match) && 'dev' === ($stabilityName = VersionParser::parseStability($reqVersion))) {
$name = strtolower($reqName);
$references[$name] = $match[1];
}
}

return $references;
}

private function guessVersion(array $config)
{
if (function_exists('proc_open')) {
$version = $this->guessGitVersion($config);
if (null !== $version) {
return $version;
}

$version = $this->guessHgVersion($config);
if (null !== $version) {
return $version;
}

return $this->guessSvnVersion($config);
}
}

private function guessGitVersion(array $config)
{
GitUtil::cleanEnv();


 if (0 === $this->process->execute('git describe --exact-match --tags', $output)) {
try {
return $this->versionParser->normalize(trim($output));
} catch (\Exception $e) {
}
}


 if (0 === $this->process->execute('git branch --no-color --no-abbrev -v', $output)) {
$branches = array();
$isFeatureBranch = false;
$version = null;


 foreach ($this->process->splitLines($output) as $branch) {
if ($branch && preg_match('{^(?:\* ) *(\(no branch\)|\(detached from \S+\)|\S+) *([a-f0-9]+) .*$}', $branch, $match)) {
if ($match[1] === '(no branch)' || substr($match[1], 0, 10) === '(detached ') {
$version = 'dev-'.$match[2];
$isFeatureBranch = true;
} else {
$version = $this->versionParser->normalizeBranch($match[1]);
$isFeatureBranch = 0 === strpos($version, 'dev-');
if ('9999999-dev' === $version) {
$version = 'dev-'.$match[1];
}
}
}

if ($branch && !preg_match('{^ *[^/]+/HEAD }', $branch)) {
if (preg_match('{^(?:\* )? *(\S+) *([a-f0-9]+) .*$}', $branch, $match)) {
$branches[] = $match[1];
}
}
}

if (!$isFeatureBranch) {
return $version;
}


 $version = $this->guessFeatureVersion($config, $version, $branches, 'git rev-list %candidate%..%branch%');

return $version;
}
}

private function guessHgVersion(array $config)
{

 if (0 === $this->process->execute('hg branch', $output)) {
$branch = trim($output);
$version = $this->versionParser->normalizeBranch($branch);
$isFeatureBranch = 0 === strpos($version, 'dev-');

if ('9999999-dev' === $version) {
$version = 'dev-'.$branch;
}

if (!$isFeatureBranch) {
return $version;
}


 $config = array('url' => getcwd());
$driver = new HgDriver($config, new NullIO(), $this->config, $this->process);
$branches = array_keys($driver->getBranches());


 $version = $this->guessFeatureVersion($config, $version, $branches, 'hg log -r "not ancestors(\'%candidate%\') and ancestors(\'%branch%\')" --template "{node}\\n"');

return $version;
}
}

private function guessFeatureVersion(array $config, $version, array $branches, $scmCmdline)
{

 
 if ((isset($config['extra']['branch-alias']) && !isset($config['extra']['branch-alias'][$version]))
|| strpos(json_encode($config), '"self.version"')
) {
$branch = preg_replace('{^dev-}', '', $version);
$length = PHP_INT_MAX;

$nonFeatureBranches = '';
if (!empty($config['non-feature-branches'])) {
$nonFeatureBranches = implode('|', $config['non-feature-branches']);
}

foreach ($branches as $candidate) {

 if ($candidate === $branch && preg_match('{^(' . $nonFeatureBranches . ')$}', $candidate)) {
return $version;
}


 if ($candidate === $branch || !preg_match('{^(master|trunk|default|develop|\d+\..+)$}', $candidate, $match)) {
continue;
}

$cmdLine = str_replace(array('%candidate%', '%branch%'), array($candidate, $branch), $scmCmdline);
if (0 !== $this->process->execute($cmdLine, $output)) {
continue;
}

if (strlen($output) < $length) {
$length = strlen($output);
$version = $this->versionParser->normalizeBranch($candidate);
if ('9999999-dev' === $version) {
$version = 'dev-'.$match[1];
}
}
}
}

return $version;
}

private function guessSvnVersion(array $config)
{
SvnUtil::cleanEnv();


 if (0 === $this->process->execute('svn info --xml', $output)) {
$trunkPath = isset($config['trunk-path']) ? preg_quote($config['trunk-path'], '#') : 'trunk';
$branchesPath = isset($config['branches-path']) ? preg_quote($config['branches-path'], '#') : 'branches';
$tagsPath = isset($config['tags-path']) ? preg_quote($config['tags-path'], '#') : 'tags';

$urlPattern = '#<url>.*/('.$trunkPath.'|('.$branchesPath.'|'. $tagsPath .')/(.*))</url>#';

if (preg_match($urlPattern, $output, $matches)) {
if (isset($matches[2]) && ($branchesPath === $matches[2] || $tagsPath === $matches[2])) {

 $version = $this->versionParser->normalizeBranch($matches[3]);
if ('9999999-dev' === $version) {
$version = 'dev-'.$matches[3];
}

return $version;
}

return $this->versionParser->normalize(trim($matches[1]));
}
}
}
}
