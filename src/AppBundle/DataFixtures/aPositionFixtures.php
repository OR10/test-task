<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Position;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class aPositionFixtures extends Fixture
{
	public function load(ObjectManager $manager)
	{
		$positions = array('CEO', 'Senior Developer', 'Middle Developer', 'Junior Developer', 'Trainee');

		$i = 1;
		foreach ($positions as $value) {
			$position = new Position();
			$position->setLevel($i);
			$position->setname($value);
			$this->addReference('position_number'.$i, $position);
			$manager->persist($position);
			$i++;
		}

		$manager->flush();
	}
}