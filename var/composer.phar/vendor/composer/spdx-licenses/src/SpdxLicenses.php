<?php










namespace Composer\Spdx;

class SpdxLicenses
{

const LICENSES_FILE = 'spdx-licenses.json';


const EXCEPTIONS_FILE = 'spdx-exceptions.json';














private $licenses;




private $licensesExpression;














private $exceptions;




private $exceptionsExpression;

public function __construct()
{
$this->loadLicenses();
$this->loadExceptions();
}













public function getLicenseByIdentifier($identifier)
{
if (!isset($this->licenses[$identifier])) {
return;
}

$license = $this->licenses[$identifier];
$license[] = 'https://spdx.org/licenses/' . $identifier . '.html#licenseText';

return $license;
}













public function getExceptionByIdentifier($identifier)
{
if (!isset($this->exceptions[$identifier])) {
return;
}

$license = $this->exceptions[$identifier];
$license[] = 'https://spdx.org/licenses/' . $identifier . '.html#licenseExceptionText';

return $license;
}








public function getIdentifierByName($name)
{
foreach ($this->licenses as $identifier => $licenseData) {
if ($licenseData[0] === $name) {
return $identifier;
}
}

foreach ($this->exceptions as $identifier => $licenseData) {
if ($licenseData[0] === $name) {
return $identifier;
}
}
}








public function isOsiApprovedByIdentifier($identifier)
{
return $this->licenses[$identifier][1];
}







public function validate($license)
{
if (is_array($license)) {
$count = count($license);
if ($count !== count(array_filter($license, 'is_string'))) {
throw new \InvalidArgumentException('Array of strings expected.');
}
$license = $count > 1 ? '(' . implode(' OR ', $license) . ')' : (string) reset($license);
}

if (!is_string($license)) {
throw new \InvalidArgumentException(sprintf(
'Array or String expected, %s given.',
gettype($license)
));
}

return $this->isValidLicenseString($license);
}




public static function getResourcesDir()
{
return dirname(__DIR__) . '/res';
}

private function loadLicenses()
{
if (null === $this->licenses) {
$jsonFile = file_get_contents(self::getResourcesDir() . '/' . self::LICENSES_FILE);
$this->licenses = json_decode($jsonFile, true);
}
}

private function loadExceptions()
{
if (null === $this->exceptions) {
$jsonFile = file_get_contents(self::getResourcesDir() . '/' . self::EXCEPTIONS_FILE);
$this->exceptions = json_decode($jsonFile, true);
}
}




private function getLicensesExpression()
{
if (null === $this->licensesExpression) {
$licenses = array_map('preg_quote', array_keys($this->licenses));
sort($licenses);
$licenses = array_reverse($licenses);
$licenses = implode('|', $licenses);
$this->licensesExpression = $licenses;
}

return $this->licensesExpression;
}




private function getExceptionsExpression()
{
if (null === $this->exceptionsExpression) {
$exceptions = array_map('preg_quote', array_keys($this->exceptions));
sort($exceptions);
$exceptions = array_reverse($exceptions);
$exceptions = implode('|', $exceptions);
$this->exceptionsExpression = $exceptions;
}

return $this->exceptionsExpression;
}







private function isValidLicenseString($license)
{
$licenses = $this->getLicensesExpression();
$exceptions = $this->getExceptionsExpression();

$regex = <<<REGEX
{
(?(DEFINE)
    # idstring: 1*( ALPHA / DIGIT / - / . )
    (?<idstring>[\pL\pN\-\.]{1,})

    # license-id: taken from list
    (?<licenseid>${licenses})

    # license-exception-id: taken from list
    (?<licenseexceptionid>${exceptions})

    # license-ref: [DocumentRef-1*(idstring):]LicenseRef-1*(idstring)
    (?<licenseref>(?:DocumentRef-(?&idstring):)?LicenseRef-(?&idstring))

    # simple-expresssion: license-id / license-id+ / license-ref
    (?<simple_expression>(?&licenseid)\+? | (?&licenseid) | (?&licenseref))

    # compound expression: 1*(
    #   simple-expression /
    #   simple-expression WITH license-exception-id /
    #   compound-expression AND compound-expression /
    #   compound-expression OR compound-expression
    # ) / ( compound-expression ) )
    (?<compound_head>
        (?&simple_expression) ( \s+ (?:with|WITH) \s+ (?&licenseexceptionid))?
            | \( \s* (?&compound_expression) \s* \)
    )
    (?<compound_expression>
        (?&compound_head) (?: \s+ (?:and|AND|or|OR) \s+ (?&compound_expression))?
    )

    # license-expression: 1*1(simple-expression / compound-expression)
    (?<license_expression>(?&compound_expression) | (?&simple_expression))
) # end of define

^(NONE | NOASSERTION | (?&license_expression))$
}x
REGEX;

$match = preg_match($regex, $license);

if (0 === $match) {
return false;
}

if (false === $match) {
throw new \RuntimeException('Regex failed to compile/run.');
}

return true;
}
}
