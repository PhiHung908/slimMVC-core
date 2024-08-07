<?php
declare(strict_types=1);

namespace hSlim\base\actions;

use hSlim\base\domain\domainException\DomainRecordNotFoundException;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Log\LoggerInterface;
use Slim\Exception\{HttpBadRequestException, HttpNotFoundException};

abstract class Action
{
	protected LoggerInterface $logger;
	
    protected Request $request;
    protected Response $response;

    protected array|string $args = [];

    //public function __construct(protected LoggerInterface &$logger)
    public function __construct()
	{
        //$this->logger = &$logger;	
	}

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args = []): Response
    {
		$this->c->set('routeName',\Slim\Routing\RouteContext::fromRequest($request)->getRoute()->getName());

        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
		
		if (empty($args)) $this->args = ['Route' => $_SERVER['QUERY_STRING']];
        
		if (!empty($_SERVER['QUERY_STRING'])) {
			$a = explode('&',$_SERVER['QUERY_STRING']);
			foreach($a as $kv) {
				$akv = explode('=',$kv . '=');
				if (!empty($akv[0]))
					$this->args[$akv[0]] = $akv[1];
			}
		}
		
		try {
			if (isset($this->args['Route']) && method_exists($this::class,'controller')) return $this->controller(); 
            else {
				if (empty($this->args)) $this->args = [];
				return $this->action();
			}
        } catch (DomainRecordNotFoundException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }
	
    /**
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @return array|object
     */
    protected function getFormData()
    {
        return $this->request->getParsedBody();
    }

    /**
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(?string $name = null)
    {
		if (empty($name)) return $this->args;
		
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param array|object|null $data
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }
}
