<?php










namespace Symfony\Component\Console\Helper;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;






abstract class Helper implements HelperInterface
{
protected $helperSet = null;






public function setHelperSet(HelperSet $helperSet = null)
{
$this->helperSet = $helperSet;
}






public function getHelperSet()
{
return $this->helperSet;
}








public static function strlen($string)
{
if (!function_exists('mb_strwidth')) {
return strlen($string);
}

if (false === $encoding = mb_detect_encoding($string)) {
return strlen($string);
}

return mb_strwidth($string, $encoding);
}

public static function formatTime($secs)
{
static $timeFormats = array(
array(0, '< 1 sec'),
array(2, '1 sec'),
array(59, 'secs', 1),
array(60, '1 min'),
array(3600, 'mins', 60),
array(5400, '1 hr'),
array(86400, 'hrs', 3600),
array(129600, '1 day'),
array(604800, 'days', 86400),
);

foreach ($timeFormats as $format) {
if ($secs >= $format[0]) {
continue;
}

if (2 == count($format)) {
return $format[1];
}

return ceil($secs / $format[2]).' '.$format[1];
}
}

public static function formatMemory($memory)
{
if ($memory >= 1024 * 1024 * 1024) {
return sprintf('%.1f GiB', $memory / 1024 / 1024 / 1024);
}

if ($memory >= 1024 * 1024) {
return sprintf('%.1f MiB', $memory / 1024 / 1024);
}

if ($memory >= 1024) {
return sprintf('%d KiB', $memory / 1024);
}

return sprintf('%d B', $memory);
}

public static function strlenWithoutDecoration(OutputFormatterInterface $formatter, $string)
{
$isDecorated = $formatter->isDecorated();
$formatter->setDecorated(false);

 $string = $formatter->format($string);

 $string = preg_replace("/\033\[[^m]*m/", '', $string);
$formatter->setDecorated($isDecorated);

return self::strlen($string);
}
}
