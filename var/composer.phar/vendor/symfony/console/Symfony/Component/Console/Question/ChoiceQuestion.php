<?php










namespace Symfony\Component\Console\Question;






class ChoiceQuestion extends Question
{
private $choices;
private $multiselect = false;
private $prompt = ' > ';
private $errorMessage = 'Value "%s" is invalid';








public function __construct($question, array $choices, $default = null)
{
parent::__construct($question, $default);

$this->choices = $choices;
$this->setValidator($this->getDefaultValidator());
$this->setAutocompleterValues(array_keys($choices));
}






public function getChoices()
{
return $this->choices;
}










public function setMultiselect($multiselect)
{
$this->multiselect = $multiselect;
$this->setValidator($this->getDefaultValidator());

return $this;
}






public function getPrompt()
{
return $this->prompt;
}








public function setPrompt($prompt)
{
$this->prompt = $prompt;

return $this;
}










public function setErrorMessage($errorMessage)
{
$this->errorMessage = $errorMessage;
$this->setValidator($this->getDefaultValidator());

return $this;
}






private function getDefaultValidator()
{
$choices = $this->choices;
$errorMessage = $this->errorMessage;
$multiselect = $this->multiselect;

return function ($selected) use ($choices, $errorMessage, $multiselect) {

 $selectedChoices = str_replace(' ', '', $selected);

if ($multiselect) {

 if (!preg_match('/^[a-zA-Z0-9_-]+(?:,[a-zA-Z0-9_-]+)*$/', $selectedChoices, $matches)) {
throw new \InvalidArgumentException(sprintf($errorMessage, $selected));
}
$selectedChoices = explode(',', $selectedChoices);
} else {
$selectedChoices = array($selected);
}

$multiselectChoices = array();
foreach ($selectedChoices as $value) {
if (empty($choices[$value])) {
throw new \InvalidArgumentException(sprintf($errorMessage, $value));
}
array_push($multiselectChoices, $choices[$value]);
}

if ($multiselect) {
return $multiselectChoices;
}

return $choices[$selected];
};
}
}
