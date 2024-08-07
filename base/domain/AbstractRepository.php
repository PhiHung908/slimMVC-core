<?php
declare(strict_types=1);

namespace hSlim\base\domain;

use Doctrine\ORM\EntityManager;
use hSlim\base\domain\DomainException\DomainRecordNotFoundException;


//use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;

abstract class AbstractRepository implements RepositoryInterface
{
	protected ContainerInterface $c;
	protected $em;
	protected $emRepository;
	//protected $db;
	private $callClass;
	
	//public function __construct(protected ContainerInterface &$c)
	public function __construct()
	{
		$this->em = $this->c->get(EntityManager::class); 
		$this->callClass = get_called_class();
		$this->emRepository = $this->em->getRepository($this->callClass);
	}
	

	/**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
		return array_values($this->emRepository->findAll($this->callClass));
    }
	
    /**
     * {@inheritdoc}
     */
    public function findById(int $id): object
    {
		$r = $this->emRepository->find($id);
		if (empty($r)) {
            throw new DomainRecordNotFoundException();
        }
        return $r;
    }
}
