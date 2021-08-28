<?php











namespace Composer;

use Composer\Package\RootPackageInterface;
use Composer\Package\Locker;
use Composer\Repository\RepositoryManager;
use Composer\Installer\InstallationManager;
use Composer\Plugin\PluginManager;
use Composer\Downloader\DownloadManager;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Autoload\AutoloadGenerator;






class Composer
{
const VERSION = 'c6cc6dd6070871f4b198ed39f76dd8047c116b02';
const BRANCH_ALIAS_VERSION = '1.0-dev';
const RELEASE_DATE = '2015-07-21 13:46:38';




private $package;




private $locker;




private $repositoryManager;




private $downloadManager;




private $installationManager;




private $pluginManager;




private $config;




private $eventDispatcher;




private $autoloadGenerator;





public function setPackage(RootPackageInterface $package)
{
$this->package = $package;
}




public function getPackage()
{
return $this->package;
}




public function setConfig(Config $config)
{
$this->config = $config;
}




public function getConfig()
{
return $this->config;
}




public function setLocker(Locker $locker)
{
$this->locker = $locker;
}




public function getLocker()
{
return $this->locker;
}




public function setRepositoryManager(RepositoryManager $manager)
{
$this->repositoryManager = $manager;
}




public function getRepositoryManager()
{
return $this->repositoryManager;
}




public function setDownloadManager(DownloadManager $manager)
{
$this->downloadManager = $manager;
}




public function getDownloadManager()
{
return $this->downloadManager;
}




public function setInstallationManager(InstallationManager $manager)
{
$this->installationManager = $manager;
}




public function getInstallationManager()
{
return $this->installationManager;
}




public function setPluginManager(PluginManager $manager)
{
$this->pluginManager = $manager;
}




public function getPluginManager()
{
return $this->pluginManager;
}




public function setEventDispatcher(EventDispatcher $eventDispatcher)
{
$this->eventDispatcher = $eventDispatcher;
}




public function getEventDispatcher()
{
return $this->eventDispatcher;
}




public function setAutoloadGenerator(AutoloadGenerator $autoloadGenerator)
{
$this->autoloadGenerator = $autoloadGenerator;
}




public function getAutoloadGenerator()
{
return $this->autoloadGenerator;
}
}
