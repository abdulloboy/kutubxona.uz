<?php











namespace Composer\Package\Version;

use Composer\Package\PackageInterface;
use Composer\Package\Link;
use Composer\Package\LinkConstraint\EmptyConstraint;
use Composer\Package\LinkConstraint\MultiConstraint;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\Loader\ArrayLoader;






class VersionParser
{
private static $modifierRegex = '[._-]?(?:(stable|beta|b|RC|alpha|a|patch|pl|p)(?:[.-]?(\d+))?)?([.-]?dev)?';

private static $stabilities = array(
'stable', 'RC', 'beta', 'alpha', 'dev',
);







public static function parseStability($version)
{
$version = preg_replace('{#.+$}i', '', $version);

if ('dev-' === substr($version, 0, 4) || '-dev' === substr($version, -4)) {
return 'dev';
}

preg_match('{'.self::$modifierRegex.'$}i', strtolower($version), $match);
if (!empty($match[3])) {
return 'dev';
}

if (!empty($match[1])) {
if ('beta' === $match[1] || 'b' === $match[1]) {
return 'beta';
}
if ('alpha' === $match[1] || 'a' === $match[1]) {
return 'alpha';
}
if ('rc' === $match[1]) {
return 'RC';
}
}

return 'stable';
}

public static function normalizeStability($stability)
{
$stability = strtolower($stability);

return $stability === 'rc' ? 'RC' : $stability;
}




public static function formatVersion(PackageInterface $package, $truncate = true)
{
trigger_error(__METHOD__.' is deprecated. Use '.
'\Composer\Package\PackageInterface::getFullPrettyVersion() instead', E_USER_DEPRECATED);

return $package->getFullPrettyVersion($truncate);
}









public function normalize($version, $fullVersion = null)
{
$version = trim($version);
if (null === $fullVersion) {
$fullVersion = $version;
}


 if (preg_match('{^([^,\s]+) +as +([^,\s]+)$}', $version, $match)) {
$version = $match[1];
}


 if (preg_match('{^([^,\s+]+)\+[^\s]+$}', $version, $match)) {
$version = $match[1];
}


 if (preg_match('{^(?:dev-)?(?:master|trunk|default)$}i', $version)) {
return '9999999-dev';
}

if ('dev-' === strtolower(substr($version, 0, 4))) {
return 'dev-'.substr($version, 4);
}


 if (preg_match('{^v?(\d{1,3})(\.\d+)?(\.\d+)?(\.\d+)?'.self::$modifierRegex.'$}i', $version, $matches)) {
$version = $matches[1]
.(!empty($matches[2]) ? $matches[2] : '.0')
.(!empty($matches[3]) ? $matches[3] : '.0')
.(!empty($matches[4]) ? $matches[4] : '.0');
$index = 5;
} elseif (preg_match('{^v?(\d{4}(?:[.:-]?\d{2}){1,6}(?:[.:-]?\d{1,3})?)'.self::$modifierRegex.'$}i', $version, $matches)) { 
 $version = preg_replace('{\D}', '-', $matches[1]);
$index = 2;
} elseif (preg_match('{^v?(\d{4,})(\.\d+)?(\.\d+)?(\.\d+)?'.self::$modifierRegex.'$}i', $version, $matches)) {
$version = $matches[1]
.(!empty($matches[2]) ? $matches[2] : '.0')
.(!empty($matches[3]) ? $matches[3] : '.0')
.(!empty($matches[4]) ? $matches[4] : '.0');
$index = 5;
}


 if (isset($index)) {
if (!empty($matches[$index])) {
if ('stable' === $matches[$index]) {
return $version;
}
$version .= '-' . $this->expandStability($matches[$index]) . (!empty($matches[$index + 1]) ? $matches[$index + 1] : '');
}

if (!empty($matches[$index + 2])) {
$version .= '-dev';
}

return $version;
}


 if (preg_match('{(.*?)[.-]?dev$}i', $version, $match)) {
try {
return $this->normalizeBranch($match[1]);
} catch (\Exception $e) {
}
}

$extraMessage = '';
if (preg_match('{ +as +'.preg_quote($version).'$}', $fullVersion)) {
$extraMessage = ' in "'.$fullVersion.'", the alias must be an exact version';
} elseif (preg_match('{^'.preg_quote($version).' +as +}', $fullVersion)) {
$extraMessage = ' in "'.$fullVersion.'", the alias source must be an exact version, if it is a branch name you should prefix it with dev-';
}

throw new \UnexpectedValueException('Invalid version string "'.$version.'"'.$extraMessage);
}








public function parseNumericAliasPrefix($branch)
{
if (preg_match('/^(?P<version>(\d+\\.)*\d+)(?:\.x)?-dev$/i', $branch, $matches)) {
return $matches['version'].".";
}

return false;
}







public function normalizeBranch($name)
{
$name = trim($name);

if (in_array($name, array('master', 'trunk', 'default'))) {
return $this->normalize($name);
}

if (preg_match('#^v?(\d+)(\.(?:\d+|[xX*]))?(\.(?:\d+|[xX*]))?(\.(?:\d+|[xX*]))?$#i', $name, $matches)) {
$version = '';
for ($i = 1; $i < 5; $i++) {
$version .= isset($matches[$i]) ? str_replace(array('*', 'X'), 'x', $matches[$i]) : '.x';
}

return str_replace('x', '9999999', $version).'-dev';
}

return 'dev-'.$name;
}









public function parseLinks($source, $sourceVersion, $description, $links)
{
trigger_error(__METHOD__.' is deprecated. Use '.
'\Composer\Package\Loader\ArrayLoader::parseLinks() instead', E_USER_DEPRECATED);
$loader = new ArrayLoader($this, false);

return $loader->parseLinks($source, $sourceVersion, $description, $links);
}







public function parseConstraints($constraints)
{
$prettyConstraint = $constraints;

if (preg_match('{^([^,\s]*?)@('.implode('|', self::$stabilities).')$}i', $constraints, $match)) {
$constraints = empty($match[1]) ? '*' : $match[1];
}

if (preg_match('{^(dev-[^,\s@]+?|[^,\s@]+?\.x-dev)#.+$}i', $constraints, $match)) {
$constraints = $match[1];
}

$orConstraints = preg_split('{\s*\|\|?\s*}', trim($constraints));
$orGroups = array();
foreach ($orConstraints as $constraints) {
$andConstraints = preg_split('{(?<!^|as|[=>< ,]) *(?<!-)[, ](?!-) *(?!,|as|$)}', $constraints);
if (count($andConstraints) > 1) {
$constraintObjects = array();
foreach ($andConstraints as $constraint) {
foreach ($this->parseConstraint($constraint) as $parsedConstraint) {
$constraintObjects[] = $parsedConstraint;
}
}
} else {
$constraintObjects = $this->parseConstraint($andConstraints[0]);
}

if (1 === count($constraintObjects)) {
$constraint = $constraintObjects[0];
} else {
$constraint = new MultiConstraint($constraintObjects);
}

$orGroups[] = $constraint;
}

if (1 === count($orGroups)) {
$constraint = $orGroups[0];
} else {
$constraint = new MultiConstraint($orGroups, false);
}

$constraint->setPrettyString($prettyConstraint);

return $constraint;
}

private function parseConstraint($constraint)
{
if (preg_match('{^([^,\s]+?)@('.implode('|', self::$stabilities).')$}i', $constraint, $match)) {
$constraint = $match[1];
if ($match[2] !== 'stable') {
$stabilityModifier = $match[2];
}
}

if (preg_match('{^[xX*](\.[xX*])*$}i', $constraint)) {
return array(new EmptyConstraint);
}

$versionRegex = '(\d+)(?:\.(\d+))?(?:\.(\d+))?(?:\.(\d+))?'.self::$modifierRegex;


 
 
 
 
 if (preg_match('{^~>?'.$versionRegex.'$}i', $constraint, $matches)) {
if (substr($constraint, 0, 2) === '~>') {
throw new \UnexpectedValueException(
'Could not parse version constraint '.$constraint.': '.
'Invalid operator "~>", you probably meant to use the "~" operator'
);
}


 if (isset($matches[4]) && '' !== $matches[4]) {
$position = 4;
} elseif (isset($matches[3]) && '' !== $matches[3]) {
$position = 3;
} elseif (isset($matches[2]) && '' !== $matches[2]) {
$position = 2;
} else {
$position = 1;
}


 $stabilitySuffix = '';
if (!empty($matches[5])) {
$stabilitySuffix .= '-' . $this->expandStability($matches[5]) . (!empty($matches[6]) ? $matches[6] : '');
}

if (!empty($matches[7])) {
$stabilitySuffix .= '-dev';
}

if (!$stabilitySuffix) {
$stabilitySuffix = "-dev";
}
$lowVersion = $this->manipulateVersionString($matches, $position, 0) . $stabilitySuffix;
$lowerBound = new VersionConstraint('>=', $lowVersion);


 
 $highPosition = max(1, $position - 1);
$highVersion = $this->manipulateVersionString($matches, $highPosition, 1) . '-dev';
$upperBound = new VersionConstraint('<', $highVersion);

return array(
$lowerBound,
$upperBound
);
}


 if (preg_match('{^\^'.$versionRegex.'($)}i', $constraint, $matches)) {

 if ('0' !== $matches[1] || '' === $matches[2]) {
$position = 1;
} elseif ('0' !== $matches[2] || '' === $matches[3]) {
$position = 2;
} else {
$position = 3;
}


 $stabilitySuffix = '';
if (empty($matches[5]) && empty($matches[7])) {
$stabilitySuffix .= '-dev';
}

$lowVersion = $this->normalize(substr($constraint . $stabilitySuffix, 1));
$lowerBound = new VersionConstraint('>=', $lowVersion);


 
 $highVersion = $this->manipulateVersionString($matches, $position, 1) . '-dev';
$upperBound = new VersionConstraint('<', $highVersion);

return array(
$lowerBound,
$upperBound
);
}


 if (preg_match('{^(\d+)(?:\.(\d+))?(?:\.(\d+))?\.[xX*]$}', $constraint, $matches)) {
if (isset($matches[3]) && '' !== $matches[3]) {
$position = 3;
} elseif (isset($matches[2]) && '' !== $matches[2]) {
$position = 2;
} else {
$position = 1;
}

$lowVersion = $this->manipulateVersionString($matches, $position) . "-dev";
$highVersion = $this->manipulateVersionString($matches, $position, 1) . "-dev";

if ($lowVersion === "0.0.0.0-dev") {
return array(new VersionConstraint('<', $highVersion));
}

return array(
new VersionConstraint('>=', $lowVersion),
new VersionConstraint('<', $highVersion),
);
}


 if (preg_match('{^(?P<from>'.$versionRegex.') +- +(?P<to>'.$versionRegex.')($)}i', $constraint, $matches)) {

 $lowStabilitySuffix = '';
if (empty($matches[6]) && empty($matches[8])) {
$lowStabilitySuffix = '-dev';
}

$lowVersion = $this->normalize($matches['from']);
$lowerBound = new VersionConstraint('>=', $lowVersion . $lowStabilitySuffix);

$empty = function ($x) {
return ($x === 0 || $x === "0") ? false : empty($x);
};

if ((!$empty($matches[11]) && !$empty($matches[12])) || !empty($matches[14]) || !empty($matches[16])) {
$highVersion = $this->normalize($matches['to']);
$upperBound = new VersionConstraint('<=', $highVersion);
} else {
$highMatch = array('', $matches[10], $matches[11], $matches[12], $matches[13]);
$highVersion = $this->manipulateVersionString($highMatch, $empty($matches[11]) ? 1 : 2, 1) . '-dev';
$upperBound = new VersionConstraint('<', $highVersion);
}

return array(
$lowerBound,
$upperBound
);
}


 if (preg_match('{^(<>|!=|>=?|<=?|==?)?\s*(.*)}', $constraint, $matches)) {
try {
$version = $this->normalize($matches[2]);

if (!empty($stabilityModifier) && $this->parseStability($version) === 'stable') {
$version .= '-' . $stabilityModifier;
} elseif ('<' === $matches[1] || '>=' === $matches[1]) {
if (!preg_match('/-' . self::$modifierRegex . '$/', strtolower($matches[2]))) {
if (substr($matches[2], 0, 4) !== 'dev-') {
$version .= '-dev';
}
}
}

return array(new VersionConstraint($matches[1] ?: '=', $version));
} catch (\Exception $e) {
}
}

$message = 'Could not parse version constraint '.$constraint;
if (isset($e)) {
$message .= ': '. $e->getMessage();
}

throw new \UnexpectedValueException($message);
}












private function manipulateVersionString($matches, $position, $increment = 0, $pad = '0')
{
for ($i = 4; $i > 0; $i--) {
if ($i > $position) {
$matches[$i] = $pad;
} elseif ($i == $position && $increment) {
$matches[$i] += $increment;

 if ($matches[$i] < 0) {
$matches[$i] = $pad;
$position--;


 if ($i == 1) {
return;
}
}
}
}

return $matches[1] . '.' . $matches[2] . '.' . $matches[3] . '.' . $matches[4];
}

private function expandStability($stability)
{
$stability = strtolower($stability);

switch ($stability) {
case 'a':
return 'alpha';
case 'b':
return 'beta';
case 'p':
case 'pl':
return 'patch';
case 'rc':
return 'RC';
default:
return $stability;
}
}







public function parseNameVersionPairs(array $pairs)
{
$pairs = array_values($pairs);
$result = array();

for ($i = 0, $count = count($pairs); $i < $count; $i++) {
$pair = preg_replace('{^([^=: ]+)[=: ](.*)$}', '$1 $2', trim($pairs[$i]));
if (false === strpos($pair, ' ') && isset($pairs[$i + 1]) && false === strpos($pairs[$i + 1], '/')) {
$pair .= ' '.$pairs[$i + 1];
$i++;
}

if (strpos($pair, ' ')) {
list($name, $version) = explode(" ", $pair, 2);
$result[] = array('name' => $name, 'version' => $version);
} else {
$result[] = array('name' => $pair);
}
}

return $result;
}
}
