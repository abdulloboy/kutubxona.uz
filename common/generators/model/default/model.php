<?php
//$UpFiles[]
$UpFiles=[];
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

use yii\db\Schema;

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{

    <?php foreach ($tableSchema->columns as $column): 
    if($column->type==Schema::TYPE_TEXT &&
      stristr($column->name,'FileName')){
        echo "public \${$column->name}_Fl;";
    $UpFiles[$column->name]=$column->name."_Fl";
    }
    endforeach; ?>

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>
        
            [[<?php 
        foreach ($relations as $name => $relation): 
           if($relation[2])continue;  
           echo "'".strtolower($name).".name',";
       endforeach; ?>],'safe'],
       <?php if($UpFiles): ?>
           [['<?= implode("','",$UpFiles) ?>'],'file'],
       <?php endif; ?>
        
        ];
    }

    public function attributes()
    {
         return array_merge(parent::attributes(), [<?php 
         foreach ($relations as $name => $relation): 
           if($relation[2])continue;  
           echo "'".strtolower($name).".name',";
       endforeach; ?>]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>

<?php foreach ($UpFiles as $name => $column): 
            $s1=ucfirst(str_ireplace('FileName','',$name)); ?>
            <?= "'$column' => " . $generator->generateString("$s1"). ",\n" ?>
<?php endforeach; ?>

<?php foreach ($relations as $name => $relation): 
     if($relation[2])
         continue; 
     echo "            '".strtolower($name).".name' => ".
       $generator->generateString($name).",\n";
endforeach; ?>

        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
}
