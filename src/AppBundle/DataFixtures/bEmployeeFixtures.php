<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Employee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraints\DateTime;

class bEmployeeFixtures extends Fixture
{
	public function load(ObjectManager $manager)
	{
		$firstNames = array('Kot', 'Dog', 'Karl', 'Kolya', 'Carol', 'Swift', 'Man', 'Dirk', 'Sergey', 'Jonas', 'Sandy', 'Liza');
		$lastNames = array('James', 'West', 'Gates', 'Jobs', 'Colyns', 'Messi', 'Ronaldo', 'Shevchenko', 'Blackberry', 'Deer');
		$thirdNames = array('Petrovych', 'Ivanych', 'Josepovych', 'Li', 'de Petite', 'Jack', 'the 2-th', 'the 3-th', 'Lostar');
		$arrayOfDates = $this->dateRange('2000-04-11', '2017-12-30');
		
		// Current object primary key
		$pk = 1;
		// Appointing one CEO of the company
		$employee = new Employee();
		$employee->setId($pk);
		$employee->setFullName($firstNames[array_rand($firstNames)].' '.$lastNames[array_rand($lastNames)].' '.$thirdNames[array_rand($thirdNames)]);
		$employee->setPositionId($this->getReference('position_number1'));
		$employee->setRecruitingDate(new \DateTime($arrayOfDates[array_rand($arrayOfDates)]));
		$employee->setSalary(5000);
		$manager->persist($employee);
		$this->addReference('parent_employee1', $employee);
		$pk++;

		// For one Senior (position #2) - 5 Middle developers
		$middlesStack = 5;
		// For one Middle (position #3) - 3 Juniors
		$juniorsStack = 3;
		// For one Junior (position #4) - 1 Trainee (position #5)
		$traineesStack = 1;
		
		for ($i = 0; $i < 4; $i++) {			
			$employee = new Employee();
			// Set parent employees for each employee (e.g. For Junior parent is Middle etc.)
			if (isset($lastSeniorId) && ($middlesStack > 0 || $juniorsStack > 0)) {
				if (isset($lastMiddleId) && ($juniorsStack > 0 || $traineesStack > 0)) {
					if (isset($lastJuniorId) && $traineesStack > 0) {
						$currentPosition = 5;
						$currentSalary = mt_rand(100, 300);
						$traineesStack--;
					} else {
						$currentPosition = 4;
						$currentSalary = mt_rand(300, 1500);
						$lastJuniorId = $pk;
						$juniorsStack--;
						// For one Junior (position #4) - 1 Trainee (position #5)
						$traineesStack = 1;
						$this->setReference('parent_employee'.$currentPosition, $employee);
					}						
				} else {
					$currentPosition = 3;
					$currentSalary = mt_rand(1500, 2500);
					$lastMiddleId = $pk;
					$middlesStack--;
					// For one Middle (position #3) - 3 Juniors
					$juniorsStack = 3;
					$this->setReference('parent_employee'.$currentPosition, $employee);
				}
			} else {
				$currentPosition = 2;
				$currentSalary = mt_rand(2500, 4000);
				$lastSeniorId = $pk;
				$lastMiddleId = null;
				$lastJuniorId = null;
				// For one Senior (position #2) - 5 Middle developers
				$middlesStack = 5;
				$this->setReference('parent_employee'.$currentPosition, $employee);
			}

			$employee->setId($pk);
			$employee->setFullName($firstNames[array_rand($firstNames)].' '.$lastNames[array_rand($lastNames)].' '.$thirdNames[array_rand($thirdNames)]);
			$employee->setPositionId($this->getReference('position_number'.$currentPosition));
			$employee->setRecruitingDate(new \DateTime($arrayOfDates[array_rand($arrayOfDates)]));
			$employee->setSalary($currentSalary);
			$employee->setParentId($this->getReference('parent_employee'.($currentPosition-1)));
			$manager->persist($employee);
			$pk++;
		}

		$manager->flush();
	}

	function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' )
	{
		$dates = array();
		$current = strtotime( $first );
		$last = strtotime( $last );
		while( $current <= $last ) {
			$dates[] = date( $format, $current );
			$current = strtotime( $step, $current );
		}

		return $dates;
	}
}