<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="position", options={"collate"="utf8_general_ci", "charset"="utf8"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PositionRepository")
 */
class Position
{
	/**
	 * @ORM\Column(name="position_id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
     * @ORM\Column(name="position_name", type="string", length=150)
     */
	private $name;

	/**
     * @ORM\Column(name="position_level", type="integer", length=2)
     */
	private $level;

	public function getId()
	{
	    return $this->id;
	}
	 
	public function setId($id)
	{
	    $this->id = $id;
	    return $this;
	}

	public function getName()
	{
	    return $this->name;
	}
	 
	public function setName($name)
	{
	    $this->name = $name;
	    return $this;
	}

	public function getLevel()
	{
	    return $this->level;
	}
	 
	public function setLevel($level)
	{
	    $this->level = $level;
	    return $this;
	}
}
