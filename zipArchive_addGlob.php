<?php

function rAddGlob($path_,$zip_){
    if(!$zip_->addGlob($path_.'*',GLOB_MARK))
        return false;
    $dirs=glob($path_.'*',
      GLOB_ONLYDIR | GLOB_MARK);
    var_dump($dirs);
    if($dirs===false)
        return false;
    foreach($dirs as $dir){
        rAddGlob($dir,$zip_);
    }
    return true;
}

$zip = new ZipArchive();
$ret = $zip->open('a.zip', ZipArchive::OVERWRITE);
if ($ret !== TRUE){
    printf("Failed with code $ret");
} else {
    if(!rAddGlob('',$zip))
        echo 'rAddGlob("./",$zip)';
}
$zip->close();
?>