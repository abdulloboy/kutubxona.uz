<?php








namespace JsonSchema;

use JsonSchema\Constraints\SchemaConstraint;
use JsonSchema\Constraints\Constraint;








class Validator extends Constraint
{
const SCHEMA_MEDIA_TYPE = 'application/schema+json';








public function check($value, $schema = null, $path = null, $i = null)
{
$validator = new SchemaConstraint($this->checkMode, $this->uriRetriever);
$validator->check($value, $schema);

$this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));
}
}
