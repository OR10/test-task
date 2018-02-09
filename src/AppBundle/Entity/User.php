<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
	/**
	 * @ORM\Column(name="user_id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
     * @ORM\Column(name="user_full_name", type="string", length=100)
     */
	private $fullName;

	/**
	 * Every user can have only one position
	 *
     * @ManyToOne(target="Position")
     * @JoinColumn(name="user_position_id", referencedColumnName="position_id")
     */
	private $position;

	/**
     * @ORM\Column(name="user_recruiting_date", type="date", length=30)
     */
	private $recruitingDate;

	/**
     * @ORM\Column(name="user_salary", type="integer", length=6)
     */
	private $salary;
}