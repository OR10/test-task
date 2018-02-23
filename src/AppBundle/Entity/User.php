<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users", options={"collate"="utf8_general_ci", "charset"="utf8"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
	/**
	 * @ORM\Column(name="user_id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(name="user_name", type="string", length=25, unique=true)
	 */
	private $username;

	/**
	 * @ORM\Column(name="user_password", type="string", length=64)
	 */
	private $password;

	/**
	 * @ORM\Column(name="user_email", type="string", length=60, unique=true)
	 */
	private $email;

	/**
	 * @ORM\Column(name="user_is_active", type="boolean")
	 */
	private $isActive;

	 /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

	public function __construct()
	{
		$this->isActive = true;
	}

	public function getUsername()
	{
		return $this->username;
	}
	 
	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	public function getPassword()
	{
		return $this->password;
	}
	 
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	public function getEmail()
	{
		return $this->email;
	}
	 
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function getIsActive()
	{
		return $this->isActive;
	}
	 
	public function setIsActive($isActive)
	{
		$this->isActive = $isActive;
		return $this;
	}

	public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

	public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
        ) = unserialize($serialized);
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }
}