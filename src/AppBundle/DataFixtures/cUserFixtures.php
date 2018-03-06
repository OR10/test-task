<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

// User only for tests (without encoding password)

class cUserFixtures extends Fixture
{
	public function __construct(UserPasswordEncoderInterface $encoder)
	{
	    $this->encoder = $encoder;
	}

	public function load(ObjectManager $manager)
	{
		$user = new User();
		$user->setUsername('kot');
		$password = $this->encoder->encodePassword($user, '1');
		$user->setPassword($password);
		$user->setEmail('kot@g.com');
		$manager->persist($user);

		$manager->flush();
	}
}