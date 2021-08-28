<?php










namespace Symfony\Component\Console\Question;






class ConfirmationQuestion extends Question
{






public function __construct($question, $default = true)
{
parent::__construct($question, (bool) $default);

$this->setNormalizer($this->getDefaultNormalizer());
}






private function getDefaultNormalizer()
{
$default = $this->getDefault();

return function ($answer) use ($default) {
if (is_bool($answer)) {
return $answer;
}

if (false === $default) {
return $answer && 'y' === strtolower($answer[0]);
}

return !$answer || 'y' === strtolower($answer[0]);
};
}
}
