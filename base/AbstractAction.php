<?php
declare(strict_types=1);

namespace hSlim\base;

use hSlim\base\domain\DynamicRepository;

use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractAction extends ActionTwig
{
	protected $model;

	private $callClass;
	
	public function __construct(protected LoggerInterface &$logger, protected ContainerInterface &$c, $assetClass = null, $modelClass = null)
    {
		parent::__construct();
		$this->callClass = get_called_class();
		
		//your code...
		$this->initModel($modelClass);
		$this->registerAsset($assetClass);
	}
	
	protected function initModel($modelClass = null)
	{
		if (empty($modelClass)) {
			if (!$this->c->has('dynamicRepository')) $dyn = new DynamicRepository($this->c, $this->callClass);
			else $dyn = $this->c->get('dynamicRepository');
		} else $dyn = new $modelClass($this->c);
		$this->model = &$dyn->model;	
	}
	
	
	protected function registerAsset($assetClass = null)
	{
		if (empty($assetClass)) {
			$assetC = "\\". array_slice(explode("\\",$this->callClass),-1,1)[0];
			if (strpos($assetC,'Action')!==false) $assetC = str_replace('Action','Asset', $assetC); else $assetC = "\\Asset";
			$clsPth = dirname($this->callClass). (strrpos($this->callClass, "\\auto_gen\\")===false ? "\\auto_gen" : "");
			if (!$this->c->get('psr4PathHelper')->findFile( $clsPth. $assetC)) $assetC = "\\Asset";
			$assetClass = $clsPth. $assetC;
		}
		$asset = new $assetClass($this->c);
		$this->assetSender = $asset->getAsset();
	}
}
