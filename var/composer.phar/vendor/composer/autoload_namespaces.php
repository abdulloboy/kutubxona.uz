<?php



$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
'Symfony\\Component\\Process\\' => array($vendorDir . '/symfony/process'),
'Symfony\\Component\\Finder\\' => array($vendorDir . '/symfony/finder'),
'Symfony\\Component\\Console\\' => array($vendorDir . '/symfony/console'),
'JsonSchema' => array($vendorDir . '/justinrainbow/json-schema/src'),
'Composer' => array($baseDir . '/src'),
);
