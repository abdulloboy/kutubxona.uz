<?php



$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
'Seld\\PharUtils\\' => array($vendorDir . '/seld/phar-utils/src'),
'Seld\\JsonLint\\' => array($vendorDir . '/seld/jsonlint/src/Seld/JsonLint'),
'Seld\\CliPrompt\\' => array($vendorDir . '/seld/cli-prompt/src'),
'Composer\\Spdx\\' => array($vendorDir . '/composer/spdx-licenses/src'),
);
