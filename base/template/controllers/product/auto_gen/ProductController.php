<?php
declare(strict_types=1);

namespace App\controllers\#TPL_PRODUCT#\auto_gen;

use App\Application\Actions\mController;

use hSlim\base\domain\domainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;

#[FastRoute( GET )]
class #U_TPL_PRODUCT#Controller extends \hSlim\base\Controllers
{
	/**
	  * @var $this->model, $this->model->emRepository,  $this->model->em
	  */
	  
	/*View note at Product/ListAction
	public function __construct(protected \Psr\Log\LoggerInterface &$logger, protected \Psr\Container\ContainerInterface &$c) {
		parent::__construct($logger, $c, __NAMESPACE__ . '\#U_TPL_PRODUCT#Asset');
	}
	//*/
	
	protected function Action(): Response
    {
		$this->response->getBody()->write('Body by Default #TPL_PRODUCT# ControllerAction');
		return $this->response;
	}
	
	protected function TestAction(): Response
	{
		$this->response->getBody()->write('Body by Direct TestAction in #TPL_PRODUCT# ControllerAction');
		return $this->response;
	}
}

class #U_TPL_PRODUCT#Asset extends \hSlim\base\AbstractAsset
{
	public function __construct(&$c)
    {
		parent::__construct($c, false, true);
	}
	
	public $sourcePath = __DIR__ . "\\auto_gen\\assets";
	
    public $depends = [
		'hSlim\assets\BootstrapAsset',
		'hSlim\assets\JqueryAsset',
	];
	
    public $js = [
	];
    
    public $css = [
	];
}