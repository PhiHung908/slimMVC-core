<?php
// src/models/#TPL_PRODUCT#/#U_TPL_PRODUCT#.php

namespace App\models\#TPL_PRODUCT#;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

use Doctrine\ORM\EntityManager;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

use Psr\Container\ContainerInterface;

use JsonSerializable;

#[Entity, Table(name: '#TPL_PRODUCT#')]
class #U_TPL_PRODUCT# extends \hSlim\base\domain\AbstractRepository implements JsonSerializable
{
	#[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
	private int|null $id = null;
    
	#[Column(type: 'string')]
	private string $name;
	
	
	public function __construct(protected ContainerInterface &$c, $data = null)
    {
		if (!empty($data)) {
			$this->id = $data['id'];
			$this->name = $data['name'];
		}
		parent::__construct();
		//your code...
	}


    public function getId(): ?int
    {
        return $this->id;
    }
	
	public function getName(): ?string
    {
        return $this->name;
    }
	
	public function toArray($id)
	{
		$dql = $this->em->createQuery("select t from " . __CLASS__ . " t where :id is null or t.id = :id")
				->setParameters(new ArrayCollection([
					new Parameter('id', $id)
				]));
		return $dql->getResult();
	}
	
	#[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
	
}
