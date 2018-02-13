<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Position;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="employee", options={"collate"="utf8_general_ci", "charset"="utf8"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmployeeRepository")
 */
class Employee
{
	public function __construct() {
        $this->children = new ArrayCollection();
    }

	/**
	 * @ORM\Column(name="employee_id", type="integer")
	 * @ORM\Id	 
	 */
	private $id;
	// * @ORM\GeneratedValue(strategy="AUTO")

	/**
     * @ORM\Column(name="employee_full_name", type="string", length=100)
     */
	private $fullName;

	/**
	 * Every employee can have only one position
	 *
     * @ORM\ManyToOne(targetEntity="Position")
     * @ORM\JoinColumn(name="employee_position_id", referencedColumnName="position_id")
     */
	private $positionId;

	/**
     * @ORM\Column(name="employee_recruiting_date", type="date", length=30)
     */
	private $recruitingDate;

	/**
     * @ORM\Column(name="employee_salary", type="integer", length=6)
     */
	private $salary;

	/**
	 * One parent employee have several subordinates ("child employees")
	 *
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="parentId")
     */
	private $children;

	/**
	 * Every employee have one parent employee
	 *
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="children")
     * @ORM\JoinColumn(name="employee_parent_id", referencedColumnName="employee_id")
     */
	private $parentId;

	public function getId()
	{
	    return $this->id;
	}
	 
	public function setId($id)
	{
	    $this->id = $id;
	    return $this;
	}

	public function getFullName()
	{
	    return $this->fullName;
	}
	 
	public function setFullName($fullName)
	{
	    $this->fullName = $fullName;
	    return $this;
	}

	public function getPositionId()
	{
	    return $this->positionId;
	}
	 
	public function setPositionId($positionId)
	{
	    $this->positionId = $positionId;
	    return $this;
	}

	public function getRecruitingDate()
	{
	    return $this->recruitingDate;
	}
	 
	public function setRecruitingDate($recruitingDate)
	{
	    $this->recruitingDate = $recruitingDate;
	    return $this;
	}

	public function getSalary()
	{
	    return $this->salary;
	}
	 
	public function setSalary($salary)
	{
	    $this->salary = $salary;
	    return $this;
	}

	public function getParentId()
	{
	    return $this->parentId;
	}
	 
	public function setParentId($parentId)
	{
	    $this->parentId = $parentId;
	    return $this;
	}

    /**
     * Add child
     *
     * @param \AppBundle\Entity\Employee $child
     *
     * @return Employee
     */
    public function addChild(\AppBundle\Entity\Employee $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\Employee $child
     */
    public function removeChild(\AppBundle\Entity\Employee $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }
}
