<?php











namespace Composer\Repository;

use Composer\Config;
use Composer\Package\PackageInterface;
use Composer\Package\CompletePackage;
use Composer\Package\Version\VersionParser;
use Composer\Plugin\PluginInterface;




class PlatformRepository extends ArrayRepository
{
const PLATFORM_PACKAGE_REGEX = '{^(?:php(?:-64bit)?|hhvm|(?:ext|lib)-[^/]+)$}i';








private $overrides = array();

public function __construct(array $packages = array(), array $overrides = array())
{
foreach ($overrides as $name => $version) {
$this->overrides[strtolower($name)] = array('name' => $name, 'version' => $version);
}
parent::__construct($packages);
}

protected function initialize()
{
parent::initialize();

$versionParser = new VersionParser();


 
 foreach ($this->overrides as $override) {

 if (!preg_match(self::PLATFORM_PACKAGE_REGEX, $override['name'])) {
throw new \InvalidArgumentException('Invalid platform package name in config.platform: '.$override['name']);
}

$version = $versionParser->normalize($override['version']);
$package = new CompletePackage($override['name'], $version, $override['version']);
$package->setDescription('Overridden virtual platform package '.$override['name']);
parent::addPackage($package);
}

$prettyVersion = PluginInterface::PLUGIN_API_VERSION;
$version = $versionParser->normalize($prettyVersion);
$composerPluginApi = new CompletePackage('composer-plugin-api', $version, $prettyVersion);
$composerPluginApi->setDescription('The Composer Plugin API');
$this->addPackage($composerPluginApi);

try {
$prettyVersion = PHP_VERSION;
$version = $versionParser->normalize($prettyVersion);
} catch (\UnexpectedValueException $e) {
$prettyVersion = preg_replace('#^([^~+-]+).*$#', '$1', PHP_VERSION);
$version = $versionParser->normalize($prettyVersion);
}

$php = new CompletePackage('php', $version, $prettyVersion);
$php->setDescription('The PHP interpreter');
$this->addPackage($php);

if (PHP_INT_SIZE === 8) {
$php64 = new CompletePackage('php-64bit', $version, $prettyVersion);
$php64->setDescription('The PHP interpreter (64bit)');
$this->addPackage($php64);
}

$loadedExtensions = get_loaded_extensions();


 foreach ($loadedExtensions as $name) {
if (in_array($name, array('standard', 'Core'))) {
continue;
}

$reflExt = new \ReflectionExtension($name);
try {
$prettyVersion = $reflExt->getVersion();
$version = $versionParser->normalize($prettyVersion);
} catch (\UnexpectedValueException $e) {
$prettyVersion = '0';
$version = $versionParser->normalize($prettyVersion);
}

$packageName = $this->buildPackageName($name);
$ext = new CompletePackage($packageName, $version, $prettyVersion);
$ext->setDescription('The '.$name.' PHP extension');
$this->addPackage($ext);
}


 
 
 foreach ($loadedExtensions as $name) {
$prettyVersion = null;
switch ($name) {
case 'curl':
$curlVersion = curl_version();
$prettyVersion = $curlVersion['version'];
break;

case 'iconv':
$prettyVersion = ICONV_VERSION;
break;

case 'intl':
$name = 'ICU';
if (defined('INTL_ICU_VERSION')) {
$prettyVersion = INTL_ICU_VERSION;
} else {
$reflector = new \ReflectionExtension('intl');

ob_start();
$reflector->info();
$output = ob_get_clean();

preg_match('/^ICU version => (.*)$/m', $output, $matches);
$prettyVersion = $matches[1];
}

break;

case 'libxml':
$prettyVersion = LIBXML_DOTTED_VERSION;
break;

case 'openssl':
$prettyVersion = preg_replace_callback('{^(?:OpenSSL\s*)?([0-9.]+)([a-z]?).*}', function ($match) {
return $match[1] . (empty($match[2]) ? '' : '.'.(ord($match[2]) - 96));
}, OPENSSL_VERSION_TEXT);
break;

case 'pcre':
$prettyVersion = preg_replace('{^(\S+).*}', '$1', PCRE_VERSION);
break;

case 'uuid':
$prettyVersion = phpversion('uuid');
break;

case 'xsl':
$prettyVersion = LIBXSLT_DOTTED_VERSION;
break;

default:

 continue 2;
}

try {
$version = $versionParser->normalize($prettyVersion);
} catch (\UnexpectedValueException $e) {
continue;
}

$lib = new CompletePackage('lib-'.$name, $version, $prettyVersion);
$lib->setDescription('The '.$name.' PHP library');
$this->addPackage($lib);
}

if (defined('HHVM_VERSION')) {
try {
$prettyVersion = HHVM_VERSION;
$version = $versionParser->normalize($prettyVersion);
} catch (\UnexpectedValueException $e) {
$prettyVersion = preg_replace('#^([^~+-]+).*$#', '$1', HHVM_VERSION);
$version = $versionParser->normalize($prettyVersion);
}

$hhvm = new CompletePackage('hhvm', $version, $prettyVersion);
$hhvm->setDescription('The HHVM Runtime (64bit)');
$this->addPackage($hhvm);
}
}




public function addPackage(PackageInterface $package)
{

 if (isset($this->overrides[strtolower($package->getName())])) {
return;
}
parent::addPackage($package);
}

private function buildPackageName($name)
{
return 'ext-' . str_replace(' ', '-', $name);
}
}
