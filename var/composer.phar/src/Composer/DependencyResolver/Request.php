<?php











namespace Composer\DependencyResolver;

use Composer\Package\LinkConstraint\LinkConstraintInterface;




class Request
{
protected $jobs;

public function __construct()
{
$this->jobs = array();
}

public function install($packageName, LinkConstraintInterface $constraint = null)
{
$this->addJob($packageName, 'install', $constraint);
}

public function update($packageName, LinkConstraintInterface $constraint = null)
{
$this->addJob($packageName, 'update', $constraint);
}

public function remove($packageName, LinkConstraintInterface $constraint = null)
{
$this->addJob($packageName, 'remove', $constraint);
}






public function fix($packageName, LinkConstraintInterface $constraint = null)
{
$this->addJob($packageName, 'install', $constraint, true);
}

protected function addJob($packageName, $cmd, LinkConstraintInterface $constraint = null, $fixed = false)
{
$packageName = strtolower($packageName);

$this->jobs[] = array(
'cmd' => $cmd,
'packageName' => $packageName,
'constraint' => $constraint,
'fixed' => $fixed
);
}

public function updateAll()
{
$this->jobs[] = array('cmd' => 'update-all');
}

public function getJobs()
{
return $this->jobs;
}
}
