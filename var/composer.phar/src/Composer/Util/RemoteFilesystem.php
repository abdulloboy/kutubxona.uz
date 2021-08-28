<?php











namespace Composer\Util;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Downloader\TransportException;






class RemoteFilesystem
{
private $io;
private $config;
private $bytesMax;
private $originUrl;
private $fileUrl;
private $fileName;
private $retry;
private $progress;
private $lastProgress;
private $options;
private $retryAuthFailure;
private $lastHeaders;
private $storeAuth;
private $degradedMode = false;








public function __construct(IOInterface $io, Config $config = null, array $options = array())
{
$this->io = $io;
$this->config = $config;
$this->options = $options;
}












public function copy($originUrl, $fileUrl, $fileName, $progress = true, $options = array())
{
return $this->get($originUrl, $fileUrl, $options, $fileName, $progress);
}











public function getContents($originUrl, $fileUrl, $progress = true, $options = array())
{
return $this->get($originUrl, $fileUrl, $options, null, $progress);
}






public function getOptions()
{
return $this->options;
}






public function getLastHeaders()
{
return $this->lastHeaders;
}















protected function get($originUrl, $fileUrl, $additionalOptions = array(), $fileName = null, $progress = true)
{
if (strpos($originUrl, '.github.com') === (strlen($originUrl) - 11)) {
$originUrl = 'github.com';
}

$this->bytesMax = 0;
$this->originUrl = $originUrl;
$this->fileUrl = $fileUrl;
$this->fileName = $fileName;
$this->progress = $progress;
$this->lastProgress = null;
$this->retryAuthFailure = true;
$this->lastHeaders = array();


 if (preg_match('{^https?://(.+):(.+)@([^/]+)}i', $fileUrl, $match)) {
$this->io->setAuthentication($originUrl, urldecode($match[1]), urldecode($match[2]));
}

if (isset($additionalOptions['retry-auth-failure'])) {
$this->retryAuthFailure = (bool) $additionalOptions['retry-auth-failure'];

unset($additionalOptions['retry-auth-failure']);
}

$options = $this->getOptionsForUrl($originUrl, $additionalOptions);

if ($this->io->isDebug()) {
$this->io->writeError((substr($fileUrl, 0, 4) === 'http' ? 'Downloading ' : 'Reading ') . $fileUrl);
}
if (isset($options['github-token'])) {
$fileUrl .= (false === strpos($fileUrl, '?') ? '?' : '&') . 'access_token='.$options['github-token'];
unset($options['github-token']);
}
if (isset($options['http'])) {
$options['http']['ignore_errors'] = true;
}
if ($this->degradedMode && substr($fileUrl, 0, 21) === 'http://packagist.org/') {

 $fileUrl = 'http://' . gethostbyname('packagist.org') . substr($fileUrl, 20);
}
$ctx = StreamContextFactory::getContext($fileUrl, $options, array('notification' => array($this, 'callbackGet')));

if ($this->progress) {
$this->io->writeError("    Downloading: <comment>Connecting...</comment>", false);
}

$errorMessage = '';
$errorCode = 0;
$result = false;
set_error_handler(function ($code, $msg) use (&$errorMessage) {
if ($errorMessage) {
$errorMessage .= "\n";
}
$errorMessage .= preg_replace('{^file_get_contents\(.*?\): }', '', $msg);
});
try {
$result = file_get_contents($fileUrl, false, $ctx);
} catch (\Exception $e) {
if ($e instanceof TransportException && !empty($http_response_header[0])) {
$e->setHeaders($http_response_header);
}
if ($e instanceof TransportException && $result !== false) {
$e->setResponse($result);
}
$result = false;
}
if ($errorMessage && !ini_get('allow_url_fopen')) {
$errorMessage = 'allow_url_fopen must be enabled in php.ini ('.$errorMessage.')';
}
restore_error_handler();
if (isset($e) && !$this->retry) {
if (!$this->degradedMode && false !== strpos($e->getMessage(), 'Operation timed out')) {
$this->degradedMode = true;
$this->io->writeError(array(
'<error>'.$e->getMessage().'</error>',
'<error>Retrying with degraded mode, check https://getcomposer.org/doc/articles/troubleshooting.md#degraded-mode for more info</error>'
));

return $this->get($this->originUrl, $this->fileUrl, $additionalOptions, $this->fileName, $this->progress);
}

throw $e;
}


 if (!empty($http_response_header[0]) && preg_match('{^HTTP/\S+ ([45]\d\d)}i', $http_response_header[0], $match)) {
$errorCode = $match[1];
if (!$this->retry) {
$e = new TransportException('The "'.$this->fileUrl.'" file could not be downloaded ('.$http_response_header[0].')', $errorCode);
$e->setHeaders($http_response_header);
$e->setResponse($result);
throw $e;
}
$result = false;
}

if ($this->progress && !$this->retry) {
$this->io->overwriteError("    Downloading: <comment>100%</comment>");
}


 if ($result && extension_loaded('zlib') && substr($fileUrl, 0, 4) === 'http') {
$decode = false;
foreach ($http_response_header as $header) {
if (preg_match('{^content-encoding: *gzip *$}i', $header)) {
$decode = true;
} elseif (preg_match('{^HTTP/}i', $header)) {
$decode = false;
}
}

if ($decode) {
try {
if (PHP_VERSION_ID >= 50400) {
$result = zlib_decode($result);
} else {

 $result = file_get_contents('compress.zlib://data:application/octet-stream;base64,'.base64_encode($result));
}

if (!$result) {
throw new TransportException('Failed to decode zlib stream');
}
} catch (\Exception $e) {
if ($this->degradedMode) {
throw $e;
}

$this->degradedMode = true;
$this->io->writeError(array(
'<error>Failed to decode response: '.$e->getMessage().'</error>',
'<error>Retrying with degraded mode, check https://getcomposer.org/doc/articles/troubleshooting.md#degraded-mode for more info</error>'
));

return $this->get($this->originUrl, $this->fileUrl, $additionalOptions, $this->fileName, $this->progress);
}
}
}


 if (false !== $result && null !== $fileName) {
if ('' === $result) {
throw new TransportException('"'.$this->fileUrl.'" appears broken, and returned an empty 200 response');
}

$errorMessage = '';
set_error_handler(function ($code, $msg) use (&$errorMessage) {
if ($errorMessage) {
$errorMessage .= "\n";
}
$errorMessage .= preg_replace('{^file_put_contents\(.*?\): }', '', $msg);
});
$result = (bool) file_put_contents($fileName, $result);
restore_error_handler();
if (false === $result) {
throw new TransportException('The "'.$this->fileUrl.'" file could not be written to '.$fileName.': '.$errorMessage);
}
}

if ($this->retry) {
$this->retry = false;

$result = $this->get($this->originUrl, $this->fileUrl, $additionalOptions, $this->fileName, $this->progress);

$authHelper = new AuthHelper($this->io, $this->config);
$authHelper->storeAuth($this->originUrl, $this->storeAuth);
$this->storeAuth = false;

return $result;
}

if (false === $result) {
$e = new TransportException('The "'.$this->fileUrl.'" file could not be downloaded: '.$errorMessage, $errorCode);
if (!empty($http_response_header[0])) {
$e->setHeaders($http_response_header);
}

if (!$this->degradedMode && false !== strpos($e->getMessage(), 'Operation timed out')) {
$this->degradedMode = true;
$this->io->writeError(array(
'<error>'.$e->getMessage().'</error>',
'<error>Retrying with degraded mode, check https://getcomposer.org/doc/articles/troubleshooting.md#degraded-mode for more info</error>'
));

return $this->get($this->originUrl, $this->fileUrl, $additionalOptions, $this->fileName, $this->progress);
}

throw $e;
}

if (!empty($http_response_header[0])) {
$this->lastHeaders = $http_response_header;
}

return $result;
}












protected function callbackGet($notificationCode, $severity, $message, $messageCode, $bytesTransferred, $bytesMax)
{
switch ($notificationCode) {
case STREAM_NOTIFY_FAILURE:
case STREAM_NOTIFY_AUTH_REQUIRED:
if (401 === $messageCode) {

 if (!$this->retryAuthFailure) {
break;
}

$this->promptAuthAndRetry($messageCode);
}
break;

case STREAM_NOTIFY_AUTH_RESULT:
if (403 === $messageCode) {

 if (!$this->retryAuthFailure) {
break;
}

$this->promptAuthAndRetry($messageCode, $message);
}
break;

case STREAM_NOTIFY_FILE_SIZE_IS:
if ($this->bytesMax < $bytesMax) {
$this->bytesMax = $bytesMax;
}
break;

case STREAM_NOTIFY_PROGRESS:
if ($this->bytesMax > 0 && $this->progress) {
$progression = round($bytesTransferred / $this->bytesMax * 100);

if ((0 === $progression % 5) && 100 !== $progression && $progression !== $this->lastProgress) {
$this->lastProgress = $progression;
$this->io->overwriteError("    Downloading: <comment>$progression%</comment>", false);
}
}
break;

default:
break;
}
}

protected function promptAuthAndRetry($httpStatus, $reason = null)
{
if ($this->config && in_array($this->originUrl, $this->config->get('github-domains'), true)) {
$message = "\n".'Could not fetch '.$this->fileUrl.', please create a GitHub OAuth token '.($httpStatus === 404 ? 'to access private repos' : 'to go over the API rate limit');
$gitHubUtil = new GitHub($this->io, $this->config, null);
if (!$gitHubUtil->authorizeOAuth($this->originUrl)
&& (!$this->io->isInteractive() || !$gitHubUtil->authorizeOAuthInteractively($this->originUrl, $message))
) {
throw new TransportException('Could not authenticate against '.$this->originUrl, 401);
}
} else {

 if ($httpStatus === 404) {
return;
}


 if (!$this->io->isInteractive()) {
if ($httpStatus === 401) {
$message = "The '" . $this->fileUrl . "' URL required authentication.\nYou must be using the interactive console to authenticate";
}
if ($httpStatus === 403) {
$message = "The '" . $this->fileUrl . "' URL could not be accessed: " . $reason;
}

throw new TransportException($message, $httpStatus);
}

 if ($this->io->hasAuthentication($this->originUrl)) {
throw new TransportException("Invalid credentials for '" . $this->fileUrl . "', aborting.", $httpStatus);
}

$this->io->overwriteError('    Authentication required (<info>'.parse_url($this->fileUrl, PHP_URL_HOST).'</info>):');
$username = $this->io->ask('      Username: ');
$password = $this->io->askAndHideAnswer('      Password: ');
$this->io->setAuthentication($this->originUrl, $username, $password);
$this->storeAuth = $this->config->get('store-auths');
}

$this->retry = true;
throw new TransportException('RETRY');
}

protected function getOptionsForUrl($originUrl, $additionalOptions)
{
if (defined('HHVM_VERSION')) {
$phpVersion = 'HHVM ' . HHVM_VERSION;
} else {
$phpVersion = 'PHP ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
}

$headers = array(
sprintf(
'User-Agent: Composer/%s (%s; %s; %s)',
Composer::VERSION === '@package_version@' ? 'source' : Composer::VERSION,
php_uname('s'),
php_uname('r'),
$phpVersion
)
);

if (extension_loaded('zlib')) {
$headers[] = 'Accept-Encoding: gzip';
}

$options = array_replace_recursive($this->options, $additionalOptions);
if (!$this->degradedMode) {

 
 $options['http']['protocol_version'] = 1.1;
$headers[] = 'Connection: close';
}

if ($this->io->hasAuthentication($originUrl)) {
$auth = $this->io->getAuthentication($originUrl);
if ('github.com' === $originUrl && 'x-oauth-basic' === $auth['password']) {
$options['github-token'] = $auth['username'];
} else {
$authStr = base64_encode($auth['username'] . ':' . $auth['password']);
$headers[] = 'Authorization: Basic '.$authStr;
}
}

if (isset($options['http']['header']) && !is_array($options['http']['header'])) {
$options['http']['header'] = explode("\r\n", trim($options['http']['header'], "\r\n"));
}
foreach ($headers as $header) {
$options['http']['header'][] = $header;
}

return $options;
}
}
