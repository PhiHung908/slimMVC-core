<?php
namespace hSlim\helper;

use RuntimeException;

use Slim\App;

use Laminas\Escaper\Escaper;
use Psr\Container\ContainerInterface;
 
class UriHelper
{
	protected $urlPath; 
	protected $routeName;
	
	public function __construct(protected ?ContainerInterface &$c = null, protected ?Escaper &$escaper = null) 
	{
		$this->routeName = $c->has('routeName') ? $c->get('routeName') : 'currentRoute';

		$rs = $c->get('psr4PathHelper')->detectModule();
		
		if (empty($rs['u'][0])) $this->urlPath = '/' . $c->get('Settings')->get('defaultRootModel') ?? 'user';
		else $this->urlPath  = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
		$countSegm = empty($rs['gModule']) ? 2 : 3;
		if (!empty($rs['aliasAsset'])) $countSegm += count(explode('/',$rs['aliasAsset']))-1;
		$this->urlPath  = rtrim(implode('/',array_slice(explode('/',$this->urlPath),0, $countSegm)),'/');	
	}
	
	
	private function _hasHttpDotDot($paramsS)
	{
		preg_match('/^\w+:\/{0,2}\w+/', $paramsS, $m);
		return isset($m[0]);
	}
	
	
	private function _doEscapeRs($rs, $paramsS)
	{
		//return $rs;
		if (stripos($rs, '?')!==false) {
			$a = explode('?',urldecode($rs));
			//$a = explode('?',$rs);
			$a[1] = implode('&',array_slice($a,1));
			//return $a[0] . '?' . $this->escaper->escapeUrl($a[1]);
			//*
			$az = explode('&',$a[1]);
			for ($i = 0; $i<count($az); $i++) {
				$ak = explode('=',$az[$i] . '=');
				$ak[1] = $this->escaper->escapeUrl($ak[1]);
				//$ak[1] = $this->escaper->escapeHtml($this->escaper->escapeUrl($ak[1]));
				//$ak[1] = str_replace('%3C','&lt;',$this->escaper->escapeUrl($ak[1]));
				$az[$i] = $ak[0] . '=' . $ak[1];
			}
			return $a[0] . '?' . implode('&',$az);
		}
		return $rs;
	}
	
	
	public function urlFor(array $params, $template = null): string
    {
		if (!isset($params['name'])) $params['name'] = 'currentRoute';
		return $this->_doEscapeRs($this->c->get('Slim\App')->getRouteCollector()->getRouteParser()->urlFor($params['name'], $params['data'] ?? [], $params['queryParams'] ?? []), $params['data'][0]);
    }

	public function fullUrlFor(array $params, $template = null): string
    {
		if (!isset($params['name'])) $params['name'] = 'currentRoute';
		return $this->_doEscapeRs($this->c->get('Slim\App')->getRouteCollector()->getRouteParser()->fullUrlFor($this->c->get('viewData')['oRequestUri'], $params['name'], $params['data'] ?? [], $params['queryParams'] ?? []), $params['data'][0]);
	}
	
	private function isRoot(&$params)
	{ 
		return substr($params['data'],0,1) == '/' || $this->_hasHttpDotDot($params['data']);
	}
	
	private function _verifyCurrUri(&$params) {
		if ($params['data'] === './' || $params['data'] === '.' || empty($params['data'])) {
			$params['data'] = $this->urlPath;
		}
	}
	
	private function _verifyParams(&$params)
	{
		$this->_verifyCurrUri($params);

		if (!is_string($params['data'])) return; 
		
		if (!isset($params['queryParams'])) $params['queryParams'] = $params[1]['queryParams'] ?? $params[1] ?? [];

		if (str_contains($params['data'],'?')) {
			$a = explode('?', $params['data']);
			$av = array_values(explode('&',$a[1]));
			$az = [];
			foreach ($av as $akv) {$x = explode('=', $akv); $az[$x[0]] = $x[1];}
			$params['queryParams'] = array_merge($az, $params['queryParams']);
			$params['data'] = $a[0];
		}
		if (!$this->isRoot($params)) {
			$params['data'] = $this->urlPath . '/' . $params['data'];
		}
	}
	
	private function _checkRouteName(&$params)
	{
		if (is_string($params)) $params = [$params];
		
		if (!isset($params['name']) && !isset($params['data']) ) {
			$params['name'] = md5(var_export($params, true));
			//$methods = $this->route->getMethods();
			
			$params['data'] = $params[0] ?? $params['url'] ?? null;
			$this->_verifyParams($params);
			/*$params['name'] = 'currentRoute';
			if (!$this->c->has($params['name'])) {
				$this->c->set($params['name'],$params['data']);
			} else { $params['data'] = $this->c->get($params['name']); $params['hasUrl'] = $this->c->get($params['name']);}
			*/
			//*
			//$r = $this->routeName; //$this->route->getName();
			//if (!$r || md5($r) !== $params['name']) {	
			if (!$this->routeName || md5($this->routeName) !== $params['name']) {
				$this->c->get('Slim\App')->map(/*$methods*/['GET'], $params['data'], function($request, $response, array $args){
					return $response;
				})->setName($params['name']);
			}
			//*/
			$params['data'] = [$params['data']];
		} else $this->_verifyCurrUri($params);
	}

	public function urlTo($params, $template = null)
	{
		$this->_checkRouteName($params);
		return $this->urlFor($params, $template);//test . '@' . $this->urlPath;
	}

	
	public function fullUrlTo($params, $template = null)
	{
		$this->_checkRouteName($params);
		if ($this->_hasHttpDotDot($params['data'][0])) return $this->urlFor($params, $template);
		return $this->fullUrlFor($params, $template);
	}
	
	public function urlGo($params, $template = null)
	{
		return $this->urlTo($params, $template);
	}
	
	public function redirect($params, $template = null)
	{
		return $this->urlTo($params, $template);
	}
	
	public function uriPath()
	{
		return $this->urlPath;
	}
}
