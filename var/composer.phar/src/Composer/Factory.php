<?php











namespace Composer;

use Composer\Config\JsonConfigSource;
use Composer\Json\JsonFile;
use Composer\IO\IOInterface;
use Composer\Package\Archiver;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableRepositoryInterface;
use Composer\Util\ProcessExecutor;
use Composer\Util\RemoteFilesystem;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Autoload\AutoloadGenerator;
use Composer\Package\Version\VersionParser;









class Factory
{




protected static function getHomeDir()
{
$home = getenv('COMPOSER_HOME');
if (!$home) {
if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
if (!getenv('APPDATA')) {
throw new \RuntimeException('The APPDATA or COMPOSER_HOME environment variable must be set for composer to run correctly');
}
$home = strtr(getenv('APPDATA'), '\\', '/') . '/Composer';
} else {
if (!getenv('HOME')) {
throw new \RuntimeException('The HOME or COMPOSER_HOME environment variable must be set for composer to run correctly');
}
$home = rtrim(getenv('HOME'), '/') . '/.composer';
}
}

return $home;
}






protected static function getCacheDir($home)
{
$cacheDir = getenv('COMPOSER_CACHE_DIR');
if (!$cacheDir) {
if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
if ($cacheDir = getenv('LOCALAPPDATA')) {
$cacheDir .= '/Composer';
} else {
$cacheDir = $home . '/cache';
}
$cacheDir = strtr($cacheDir, '\\', '/');
} else {
$cacheDir = $home.'/cache';
}
}

return $cacheDir;
}





public static function createConfig(IOInterface $io = null, $cwd = null)
{
$cwd = $cwd ?: getcwd();


 $home = self::getHomeDir();
$cacheDir = self::getCacheDir($home);


 
 
 foreach (array($home, $cacheDir) as $dir) {
if (!file_exists($dir . '/.htaccess')) {
if (!is_dir($dir)) {
@mkdir($dir, 0777, true);
}
@file_put_contents($dir . '/.htaccess', 'Deny from all');
}
}

$config = new Config(true, $cwd);


 $config->merge(array('config' => array('home' => $home, 'cache-dir' => $cacheDir)));


 $file = new JsonFile($config->get('home').'/config.json');
if ($file->exists()) {
if ($io && $io->isDebug()) {
$io->writeError('Loading config file ' . $file->getPath());
}
$config->merge($file->read());
}
$config->setConfigSource(new JsonConfigSource($file));


 $file = new JsonFile($config->get('home').'/auth.json');
if ($file->exists()) {
if ($io && $io->isDebug()) {
$io->writeError('Loading config file ' . $file->getPath());
}
$config->merge(array('config' => $file->read()));
}
$config->setAuthConfigSource(new JsonConfigSource($file, true));

return $config;
}

public static function getComposerFile()
{
return trim(getenv('COMPOSER')) ?: './composer.json';
}

public static function createAdditionalStyles()
{
return array(
'highlight' => new OutputFormatterStyle('red'),
'warning' => new OutputFormatterStyle('black', 'yellow'),
);
}

public static function createDefaultRepositories(IOInterface $io = null, Config $config = null, RepositoryManager $rm = null)
{
$repos = array();

if (!$config) {
$config = static::createConfig($io);
}
if (!$rm) {
if (!$io) {
throw new \InvalidArgumentException('This function requires either an IOInterface or a RepositoryManager');
}
$factory = new static;
$rm = $factory->createRepositoryManager($io, $config);
}

foreach ($config->getRepositories() as $index => $repo) {
if (is_string($repo)) {
throw new \UnexpectedValueException('"repositories" should be an array of repository definitions, only a single repository was given');
}
if (!is_array($repo)) {
throw new \UnexpectedValueException('Repository "'.$index.'" ('.json_encode($repo).') should be an array, '.gettype($repo).' given');
}
if (!isset($repo['type'])) {
throw new \UnexpectedValueException('Repository "'.$index.'" ('.json_encode($repo).') must have a type defined');
}
$name = is_int($index) && isset($repo['url']) ? preg_replace('{^https?://}i', '', $repo['url']) : $index;
while (isset($repos[$name])) {
$name .= '2';
}
$repos[$name] = $rm->createRepository($repo['type'], $repo);
}

return $repos;
}













public function createComposer(IOInterface $io, $localConfig = null, $disablePlugins = false, $cwd = null, $fullLoad = true)
{
$cwd = $cwd ?: getcwd();


 if (null === $localConfig) {
$localConfig = static::getComposerFile();
}

if (is_string($localConfig)) {
$composerFile = $localConfig;
$file = new JsonFile($localConfig, new RemoteFilesystem($io));

if (!$file->exists()) {
if ($localConfig === './composer.json' || $localConfig === 'composer.json') {
$message = 'Composer could not find a composer.json file in '.$cwd;
} else {
$message = 'Composer could not find the config file: '.$localConfig;
}
$instructions = 'To initialize a project, please create a composer.json file as described in the https://getcomposer.org/ "Getting Started" section';
throw new \InvalidArgumentException($message.PHP_EOL.$instructions);
}

$file->validateSchema(JsonFile::LAX_SCHEMA);
$localConfig = $file->read();
}


 $config = static::createConfig($io, $cwd);
$config->merge($localConfig);
if (isset($composerFile)) {
if ($io && $io->isDebug()) {
$io->writeError('Loading config file ' . $composerFile);
}
$localAuthFile = new JsonFile(dirname(realpath($composerFile)) . '/auth.json');
if ($localAuthFile->exists()) {
if ($io && $io->isDebug()) {
$io->writeError('Loading config file ' . $localAuthFile->getPath());
}
$config->merge(array('config' => $localAuthFile->read()));
$config->setAuthConfigSource(new JsonConfigSource($localAuthFile, true));
}
}

$vendorDir = $config->get('vendor-dir');
$binDir = $config->get('bin-dir');


 $composer = new Composer();
$composer->setConfig($config);

if ($fullLoad) {

 $io->loadConfiguration($config);
}


 $dispatcher = new EventDispatcher($composer, $io);
$composer->setEventDispatcher($dispatcher);


 $rm = $this->createRepositoryManager($io, $config, $dispatcher);
$composer->setRepositoryManager($rm);


 $this->addLocalRepository($rm, $vendorDir);


 $parser = new VersionParser;
$loader = new Package\Loader\RootPackageLoader($rm, $config, $parser, new ProcessExecutor($io));
$package = $loader->load($localConfig);
$composer->setPackage($package);


 $im = $this->createInstallationManager();
$composer->setInstallationManager($im);

if ($fullLoad) {

 $dm = $this->createDownloadManager($io, $config, $dispatcher);
$composer->setDownloadManager($dm);


 $generator = new AutoloadGenerator($dispatcher, $io);
$composer->setAutoloadGenerator($generator);
}


 $this->createDefaultInstallers($im, $composer, $io);

if ($fullLoad) {
$globalComposer = $this->createGlobalComposer($io, $config, $disablePlugins);
$pm = $this->createPluginManager($io, $composer, $globalComposer);
$composer->setPluginManager($pm);

if (!$disablePlugins) {
$pm->loadInstalledPlugins();
}


 
 if ($rm->getLocalRepository()) {
$this->purgePackages($rm->getLocalRepository(), $im);
}
}


 if ($fullLoad && isset($composerFile)) {
$lockFile = "json" === pathinfo($composerFile, PATHINFO_EXTENSION)
? substr($composerFile, 0, -4).'lock'
: $composerFile . '.lock';
$locker = new Package\Locker($io, new JsonFile($lockFile, new RemoteFilesystem($io, $config)), $rm, $im, md5_file($composerFile));
$composer->setLocker($locker);
}

return $composer;
}







protected function createRepositoryManager(IOInterface $io, Config $config, EventDispatcher $eventDispatcher = null)
{
$rm = new RepositoryManager($io, $config, $eventDispatcher);
$rm->setRepositoryClass('composer', 'Composer\Repository\ComposerRepository');
$rm->setRepositoryClass('vcs', 'Composer\Repository\VcsRepository');
$rm->setRepositoryClass('package', 'Composer\Repository\PackageRepository');
$rm->setRepositoryClass('pear', 'Composer\Repository\PearRepository');
$rm->setRepositoryClass('git', 'Composer\Repository\VcsRepository');
$rm->setRepositoryClass('svn', 'Composer\Repository\VcsRepository');
$rm->setRepositoryClass('perforce', 'Composer\Repository\VcsRepository');
$rm->setRepositoryClass('hg', 'Composer\Repository\VcsRepository');
$rm->setRepositoryClass('artifact', 'Composer\Repository\ArtifactRepository');

return $rm;
}





protected function addLocalRepository(RepositoryManager $rm, $vendorDir)
{
$rm->setLocalRepository(new Repository\InstalledFilesystemRepository(new JsonFile($vendorDir.'/composer/installed.json')));
}





protected function createGlobalComposer(IOInterface $io, Config $config, $disablePlugins)
{
if (realpath($config->get('home')) === getcwd()) {
return;
}

$composer = null;
try {
$composer = self::createComposer($io, $config->get('home') . '/composer.json', $disablePlugins, $config->get('home'), false);
} catch (\Exception $e) {
if ($io->isDebug()) {
$io->writeError('Failed to initialize global composer: '.$e->getMessage());
}
}

return $composer;
}







public function createDownloadManager(IOInterface $io, Config $config, EventDispatcher $eventDispatcher = null)
{
$cache = null;
if ($config->get('cache-files-ttl') > 0) {
$cache = new Cache($io, $config->get('cache-files-dir'), 'a-z0-9_./');
}

$dm = new Downloader\DownloadManager($io);
switch ($config->get('preferred-install')) {
case 'dist':
$dm->setPreferDist(true);
break;
case 'source':
$dm->setPreferSource(true);
break;
case 'auto':
default:

 break;
}

$dm->setDownloader('git', new Downloader\GitDownloader($io, $config));
$dm->setDownloader('svn', new Downloader\SvnDownloader($io, $config));
$dm->setDownloader('hg', new Downloader\HgDownloader($io, $config));
$dm->setDownloader('perforce', new Downloader\PerforceDownloader($io, $config));
$dm->setDownloader('zip', new Downloader\ZipDownloader($io, $config, $eventDispatcher, $cache));
$dm->setDownloader('rar', new Downloader\RarDownloader($io, $config, $eventDispatcher, $cache));
$dm->setDownloader('tar', new Downloader\TarDownloader($io, $config, $eventDispatcher, $cache));
$dm->setDownloader('gzip', new Downloader\GzipDownloader($io, $config, $eventDispatcher, $cache));
$dm->setDownloader('phar', new Downloader\PharDownloader($io, $config, $eventDispatcher, $cache));
$dm->setDownloader('file', new Downloader\FileDownloader($io, $config, $eventDispatcher, $cache));

return $dm;
}







public function createArchiveManager(Config $config, Downloader\DownloadManager $dm = null)
{
if (null === $dm) {
$io = new IO\NullIO();
$io->loadConfiguration($config);
$dm = $this->createDownloadManager($io, $config);
}

$am = new Archiver\ArchiveManager($dm);
$am->addArchiver(new Archiver\PharArchiver);

return $am;
}







protected function createPluginManager(IOInterface $io, Composer $composer, Composer $globalComposer = null)
{
return new Plugin\PluginManager($io, $composer, $globalComposer);
}




protected function createInstallationManager()
{
return new Installer\InstallationManager();
}






protected function createDefaultInstallers(Installer\InstallationManager $im, Composer $composer, IOInterface $io)
{
$im->addInstaller(new Installer\LibraryInstaller($io, $composer, null));
$im->addInstaller(new Installer\PearInstaller($io, $composer, 'pear-library'));
$im->addInstaller(new Installer\PluginInstaller($io, $composer));
$im->addInstaller(new Installer\MetapackageInstaller($io));
}





protected function purgePackages(WritableRepositoryInterface $repo, Installer\InstallationManager $im)
{
foreach ($repo->getPackages() as $package) {
if (!$im->isPackageInstalled($repo, $package)) {
$repo->removePackage($package);
}
}
}








public static function create(IOInterface $io, $config = null, $disablePlugins = false)
{
$factory = new static();

return $factory->createComposer($io, $config, $disablePlugins);
}
}
