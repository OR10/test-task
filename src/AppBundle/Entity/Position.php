<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="position")
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
}