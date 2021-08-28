<?php










namespace Symfony\Component\Console\Helper;







class TableStyle
{
private $paddingChar = ' ';
private $horizontalBorderChar = '-';
private $verticalBorderChar = '|';
private $crossingChar = '+';
private $cellHeaderFormat = '<info>%s</info>';
private $cellRowFormat = '%s';
private $cellRowContentFormat = ' %s ';
private $borderFormat = '%s';
private $padType = STR_PAD_RIGHT;








public function setPaddingChar($paddingChar)
{
if (!$paddingChar) {
throw new \LogicException('The padding char must not be empty');
}

$this->paddingChar = $paddingChar;

return $this;
}






public function getPaddingChar()
{
return $this->paddingChar;
}








public function setHorizontalBorderChar($horizontalBorderChar)
{
$this->horizontalBorderChar = $horizontalBorderChar;

return $this;
}






public function getHorizontalBorderChar()
{
return $this->horizontalBorderChar;
}








public function setVerticalBorderChar($verticalBorderChar)
{
$this->verticalBorderChar = $verticalBorderChar;

return $this;
}






public function getVerticalBorderChar()
{
return $this->verticalBorderChar;
}








public function setCrossingChar($crossingChar)
{
$this->crossingChar = $crossingChar;

return $this;
}






public function getCrossingChar()
{
return $this->crossingChar;
}








public function setCellHeaderFormat($cellHeaderFormat)
{
$this->cellHeaderFormat = $cellHeaderFormat;

return $this;
}






public function getCellHeaderFormat()
{
return $this->cellHeaderFormat;
}








public function setCellRowFormat($cellRowFormat)
{
$this->cellRowFormat = $cellRowFormat;

return $this;
}






public function getCellRowFormat()
{
return $this->cellRowFormat;
}








public function setCellRowContentFormat($cellRowContentFormat)
{
$this->cellRowContentFormat = $cellRowContentFormat;

return $this;
}






public function getCellRowContentFormat()
{
return $this->cellRowContentFormat;
}








public function setBorderFormat($borderFormat)
{
$this->borderFormat = $borderFormat;

return $this;
}






public function getBorderFormat()
{
return $this->borderFormat;
}








public function setPadType($padType)
{
$this->padType = $padType;

return $this;
}






public function getPadType()
{
return $this->padType;
}
}
