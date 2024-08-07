<?php
declare(strict_types=1);

namespace hSlim\base\domain;

use hSlim\base\domain\RepositoryInterface;

use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

class DynamicRepository implements RepositoryInterface
{
	public $model;
	
	public function __construct(ContainerInterface &$c, string $actionCallClass = null, bool $inMemory = false)
    {
		$modelName  = null;
		if (empty($actionCallClass)) {
			$rs = $c->get('psr4PathHelper')->detectModule();
			$modelName = empty($rs['u'][0]) ? ($c->get('Settings')->get('defaultRootModel') ?? 'user') :  $rs['u'][0];
			$callClass = $modelName;
		} else {
			$callClass = dirname($actionCallClass);
			if (substr($callClass, strrpos($callClass,"\\")+1)=='auto_gen') {
				$callClass = dirname($callClass);
			}
		}
		
		$a = explode("\\", $callClass);
		
		if ($inMemory && count($a)>2) array_pop($a);
			
		$modelName = $modelName ?? array_pop($a);
		
		$modelClass = ($inMemory ? "App\\controllers" : '') . implode("\\",$a);
		array_pop($a); 
		if (!$inMemory) $this->model =  new (implode("\\",$a) . "\\models\\$modelName\\".ucfirst($modelName))($c);
		else $this->model =  new ("App\\models\\$modelName\\InMemory" . ucfirst($modelName) . "Repository")($c);
		
	}
	
	
	public function findById(int $id): object
	{
		return $this->model->findById($id);
	}
	
	
	/**
     * @return Product[]
     */
    public function findAll(): array
	{
		return $this->model->findAll();
	}
}
