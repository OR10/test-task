<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Employee;
use AppBundle\Entity\Position;
use AppBundle\Form\ImageType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends Controller
{
	/**
	 * @Route("/", name="homepage")
	 */
	public function indexAction(Request $request)
	{      
		$employeesArr = $this->getEmployees();		
		if ($employeesArr != false) {
			foreach ($employeesArr as $key => $value) {
				if ($value->getPositionId()->getLevel() == '1') {
					$director = $value;
					unset($employeesArr[$key]);
					break;
				}
			}
		} else {
			$this->addFlash(
	            'danger',
	            'There are some troubles with database structure'
	        );
	        return $this->render('default/main.html.twig');
		}
		// Prepare employees tree to show
		if (isset($director)) {
			$orphansArr = [];
			$isLastElement = false;
			do {
				$res = $this->restructurateTree($director, $employeesArr, $orphansArr, $isLastElement);
				$employeesArr = $res[0];
				$orphansArr = $res[1];
				$isLastElement = $res[2];
			} while (sizeof($orphansArr) > 0 || $isLastElement == false);
			array_unshift($employeesArr, $director);
		}

	    $pagination = $this->getPagination($employeesArr, $request);

		return $this->render('default/main.html.twig', [
			'employees_list' => $employeesArr,
			'pagination' => $pagination
		]);
	}	

	public function restructurateTree($director, $employeesArr, $orphansArr = array(), $isLastElement)
	{
		$lastParent = $director;
		if (sizeof($orphansArr) > 0) {
			// Find next Senior to start new branch of employees
			foreach ($orphansArr as $keyOrph => $orphan) {
				if ($orphan->getParentId()->getId() == $lastParent->getId()) {
					unset($orphansArr[$keyOrph]);
					array_unshift($employeesArr, $orphan);
					break;
				}
			}	
		}
		$lastRealIndex = 0; // Because of $keyEmpl is not correct when do array_splice() as it not consider unsetting elements from $employeesArr
		foreach ($employeesArr as $keyEmpl => $employee) {
			if ($lastRealIndex == sizeof($employeesArr) - 1) {
				$isLastElement = true;
			}
			if ($isLastElement == true && sizeof($orphansArr) == 0) {
				break;
			}
			if ($employee->getPositionId()->getLevel() == 2) {
				// It's Senior. It's because of they can be descendants only of the $director that can be set only manually
				$lastParent = $director;
			}
			if ($employee->getPositionId()->getLevel() <= $lastParent->getPositionId()->getLevel()) {
				// It is expected to find a bigger level parent in previous 20 employees
				if ($lastRealIndex - 20 > 0) {
					$startPos = $lastRealIndex - 20;
				} else {
					$startPos = 0;
				}
				$employeesBeforeCurrent = array_slice($employeesArr, $startPos, $lastRealIndex - $startPos);
				$employeesBeforeCurrent = array_reverse($employeesBeforeCurrent);
				$isWrongParent = false;
				foreach ($employeesBeforeCurrent as $keyP => $potentialParent) {
					// Get the first bigger level parent and check if he is the right parent
					if ($employee->getPositionId()->getLevel() - $potentialParent->getPositionId()->getLevel() == 1) {
						$lastParent = $potentialParent;
						if ($employee->getParentId()->getId() != $potentialParent->getId()) {
							$orphansArr[] = $employee;
							unset($employeesArr[$keyEmpl]);
							$isWrongParent = true;
						}
						break;
					}
				}
				if ($isWrongParent == true) {
					continue;
				}
			}
			if ($employee->getParentId()->getId() == $lastParent->getId()) {
				if (sizeof($orphansArr) > 0) {
					$lastParent = $employee;
					$directDescendants = [];
					foreach ($orphansArr as $keyOrph => $orphan) {
						if ($orphan->getParentId()->getId() == $lastParent->getId()) {
							$directDescendants[] = $orphan;
							$lastParent = $orphan;
							unset($orphansArr[$keyOrph]);
						}
					}
					if (sizeof($directDescendants) > 0) {
						array_splice($employeesArr, $lastRealIndex+1, 0, $directDescendants);
						break;
					}
				} else {
					$lastParent = $employee;
				}
				$lastRealIndex++;
			} else {
				$orphansArr[] = $employee;
				unset($employeesArr[$keyEmpl]);
			}
		}		

		return [$employeesArr, $orphansArr, $isLastElement];
	}

	/**
	 * @Route("/employees", name="employees_list")
	 */
	public function showEmployeesListAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$employeeRepository = $this->getDoctrine()->getRepository('AppBundle:Employee');
		if (null !== $request->request->get('sort') && null !== $request->request->get('order')) {
			$orderBy = $request->request->get('order');
			$sortBy = $request->request->get('sort');
			// Sort search results
			if (null !== $request->request->get('searchWord') && !empty($request->request->get('searchWord'))) {
				$query = $em->createQuery("SELECT e FROM AppBundle:Employee e 
				JOIN AppBundle:Position p 
				WITH e.positionId = p.id 
				WHERE e.fullName LIKE :word
				OR p.name LIKE :word 
				OR e.salary LIKE :word
				OR e.recruitingDate LIKE :word
				OR e.id LIKE :word 
				ORDER BY e.$orderBy $sortBy")
				->setParameter('word', '%'.$request->request->get('searchWord').'%');
				$employeesArr = $query->getResult();
			} else {
				// Sort all records
				$employeesArr = $employeeRepository->findBy(array(), array($orderBy => $sortBy));
			}
			if ($sortBy == 'asc') {
				$sortBy = 'desc';
			} else {
				$sortBy = 'asc';
			}
			$pagination = $this->getPagination($employeesArr, $request);
			$template = $this->get('twig')
				->loadTemplate("default/employees.html.twig");
			$newTable = $template->renderBlock("table", array(
				'employees_list' => $employeesArr,
				'sortBy' => $sortBy,
				'pagination' => $pagination));

			return new Response(
				json_encode(array(
					'newTable' => $newTable
				)
			));
		} elseif (null !== $request->request->get('searchWord') && strlen($request->request->get('searchWord')) > 0) {
			$query = $em->createQuery("SELECT e FROM AppBundle:Employee e 
				JOIN AppBundle:Position p 
				WITH e.positionId = p.id 
				WHERE e.fullName LIKE :word
				OR p.name LIKE :word 
				OR e.salary LIKE :word
				OR e.recruitingDate LIKE :word
				OR e.id LIKE :word")
				->setParameter('word', '%'.$request->request->get('searchWord').'%');

			$employeesArr = $query->getResult();
			if (sizeof($employeesArr) == 0) {
				$msg = 'No results by your search word!';
			} else {
				$pagination = $this->getPagination($employeesArr, $request);
				$template = $this->get('twig')
					->loadTemplate("default/employees.html.twig");
				$newTable = $template->renderBlock("table", array(
					'employees_list' => $employeesArr,
					'pagination' => $pagination));
			}
			return new Response (
				json_encode(array(
					'newTable' => isset($newTable) ? $newTable : false,
					'msg' => isset($msg) ? $msg : ''
				)
			));
																																													
		}

		$employee = new Employee();
		$form = $this->createForm(ImageType::class, $employee, array(
			'action' => $this->generateUrl('employee_add'),));
        $form->handleRequest($request);

		$employeesArr = $this->getEmployees();
		if ($employeesArr == false) {
			$this->addFlash(
	            'danger',
	            'There are some troubles with database structure'
	        );
	        return $this->render('default/main.html.twig');
		}
		$positionsArr = $this->getDoctrine()->getRepository('AppBundle:Position')->findAll();

	    $pagination = $this->getPagination($employeesArr, $request);

		return $this->render('default/employees.html.twig', [
			'employees_list' => $employeesArr,
			'positions_list' => $positionsArr,
			'sortBy' => 'asc',
			'form' => $form->createView(),
			'pagination' => $pagination
		]);
	}

	/**
	 * @Route("/update-employee", name="employee_update")
	 */
	public function updateEmployeeAction(Request $request)
	{
		$employeeId = null !== $request->request->get('employeeId') ? $request->request->get('employeeId') : false;
		$fullName = null !== $request->request->get('fullName') ? $request->request->get('fullName') : false;
		$positionId = null !== $request->request->get('positionId') ? $request->request->get('positionId') : false;
		$salary = null !== $request->request->get('salary') ? $request->request->get('salary') : false;
		$parentId = null !== $request->request->get('parentId') ? $request->request->get('parentId') : false;
		$recruitingDate = null !== $request->request->get('recruitingDate') ? $request->request->get('recruitingDate') : false;

		$em = $this->getDoctrine()->getManager();
		$employeeRepository = $em->getRepository('AppBundle:Employee');
		
		$employee = $employeeRepository->findOneById($employeeId);
		if ($employee) {
			$error = '';
			if ($fullName != false && $fullName != '') {
				$employee->setFullName($fullName);				
			} else {
				$error .= "Full name is empty!\n";
			}
			if ($salary != false && is_numeric($salary)) {
				$employee->setSalary($salary);
			} else {
				$error .= "Something wrong with salary! Maybe it is too small?)\n";
			}
			if ($positionId != false) {
				// Get new position
				$position = $em->getRepository('AppBundle:Position')->findOneById($positionId);
				if ($position) {
					$employee->setPositionId($position);					
				} else {
					$error .= "Something wrong with position!\n";
				}
			} else {
				$error .= "Something wrong with position!\n";
			}
			if ($parentId != false) {
				// Get new parent
				$parent = $employeeRepository->findOneById($parentId);
				if ($parent) {
					$employee->setParentId($parent);
				} else {
					$error .= "Something wrong with parent! Maybe last is crazy?)\n";
				}
			} else {
				$error .= "Something wrong with parent! Maybe last is crazy?)\n";
			}
			if ($recruitingDate != false && $this->isRealDate($recruitingDate)) {
				$employee->setRecruitingDate(new \DateTime($recruitingDate));
			} else {
				$error .= "Uncorrect date!\n";
			}
			if (null !== $request->files->get('image_image')) {
				$file = $request->files->get('image_image');
		        $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
		        $file->move(
		            $this->getParameter('headshots_directory'),
		            $fileName
		        );
		        $employee->setImage($fileName);
			}

			if ($error != '') {
				return new Response (
					json_encode(array(
						'error' => $error
					)
				));
			} else {
				$em->persist($employee);
				$em->flush();		
			}
		} else {
			return new Response (
				json_encode(array(
					'error' => $employeeId
				)
			));
		}

		$employeesArr = $employeeRepository->findAll();
		$pagination = $this->getPagination($employeesArr, $request);
		$pagination->setUsedRoute('employees_list');
		$template = $this->get('twig')
			->loadTemplate("default/employees.html.twig");
		$newTable = $template->renderBlock("table", array(
			'employees_list' => $employeesArr,
			'pagination' => $pagination));

		return new Response (
			json_encode(array(
				'newTable' => isset($newTable) ? $newTable : false
			)
		));
	}

	/**
	 * @Route("/add-employee", name="employee_add")
	 */
	public function addEmployeeAction(Request $request)
	{
		$employee = new Employee();
		$form = $this->createForm(ImageType::class, $employee);
        $form->handleRequest($request);

		$em = $this->getDoctrine()->getManager();
		$employeeRepository = $em->getRepository('AppBundle:Employee');
		// Add first CEO if no employees yet
		$fullName = null !== $request->request->get('fullName') ? $request->request->get('fullName') : false;
		$positionId = null !== $request->request->get('positionId') ? $request->request->get('positionId') : false;
		$salary = null !== $request->request->get('salary') ? $request->request->get('salary') : false;
		$recruitingDate = null !== $request->request->get('recruitingDate') ? $request->request->get('recruitingDate') : false;
		$parentId = null !== $request->request->get('parentId') ? $request->request->get('parentId') : false;
		if ($parentId == 0) {
			$parent = null;
		} else {
			$parentEmployee = $employeeRepository->findOneById($parentId);
			$parent = $em->getReference('AppBundle:Employee', $parentEmployee->getId());
		}
		$position = $this->getDoctrine()->getRepository('AppBundle:Position')->findOneById($positionId);
		if ($position) {
			$lastId = $em->createQuery('SELECT e.id FROM AppBundle:Employee e ORDER BY e.id DESC')
				->setMaxResults(1)
				->getResult();
			if ($lastId && isset($parentEmployee)) {
				$pk = $lastId[0]['id'] + 1;
			} else {
				$pk = 1;
			}

			if (null !== $request->files->get('image_image')) {
	            $file = $request->files->get('image_image');
	            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
	            $file->move(
	                $this->getParameter('headshots_directory'),
	                $fileName
	            );
	            $employee->setImage($fileName);
	        }
			$employee->setId($pk);
			$employee->setFullName($fullName);
			$employee->setPositionId($em->getReference('AppBundle:Position', $position->getId()));
			$employee->setRecruitingDate(new \DateTime($recruitingDate));
			$employee->setParentId($parent);
			$employee->setSalary($salary);
			$em->persist($employee);
			$em->flush();
		} else {
			$error = 'Some error with position? -_-';
			return new Response (
				json_encode(array(
					'error' => $error,
				)
			));
		}

		$employeesArr = $employeeRepository->findAll();
		$pagination = $this->getPagination($employeesArr, $request);
		$pagination->setUsedRoute('employees_list');
		$template = $this->get('twig')
			->loadTemplate("default/employees.html.twig");
		$newTable = $template->renderBlock("table", array(
			'employees_list' => $employeesArr,
			'pagination' => $pagination));
		$parentEmployeesBlock = $template->renderBlock("parentEmployee", array(
			'employees_list' => $employeesArr,
			'pagination' => $pagination));

		return new Response (
			json_encode(array(
				'newTable' => isset($newTable) ? $newTable : false,
				'parentEmployee' => isset($parentEmployeesBlock) ? $parentEmployeesBlock : false
			)
		));
	}

	/**
	 * @Route("/delete-employee/{employeeId}", name="employee_delete")
	 */
	public function deleteEmployeeAction(Request $request, $employeeId)
	{
		$em = $this->getDoctrine()->getManager();
		$employeeRepository = $em->getRepository('AppBundle:Employee');
		$employee = $employeeRepository->findOneById($employeeId);
		if ($employee) {
			if ($employee->getParentId() != null) {
				$parentId = $employee->getParentId()->getId();				
			} else {
				$this->addFlash(
		            'danger',
		            'Do you want to remove the CEO? First set new one like a parent for current director.'
		        );
		        return $this->redirectToRoute('employees_list');
			}
			$parentOfCurrent = $em->getReference('AppBundle:Employee', $parentId);
			$employeeChildren = $employeeRepository->findBy(array('parentId' => $employeeId));
			foreach ($employeeChildren as $childEmployee) {
				$childEmployee->setParentId($parentOfCurrent);
			}
			$em->remove($employee);
			$em->flush();
		
			$this->addFlash(
	            'success',
	            'User was successfully deleted'
	        );
		}

        return $this->redirectToRoute('employees_list');
	}

	public function getEmployees()
	{
		$schemaManager = $this->getDoctrine()->getConnection()->getSchemaManager();
		if ($schemaManager->tablesExist(array('employee')) == true) {
			$employees = $this->getDoctrine()->getRepository('AppBundle:Employee')->findAll();		
		} else {			
			return false;
		}

		return $employees;
	}

	public function isRealDate($date) { 
	    if (false === strtotime($date)) { 
	        return false;
	    } 
	    list($year, $month, $day) = explode('-', $date); 
	 
	    return checkdate($month, $day, $year);
	}

	private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    public function getPagination($query, $request)
    { 
    	$paginator  = $this->get('knp_paginator');
	    $pagination = $paginator->paginate(
	        $query,
	        $request->query->getInt('page', 1),
	        $request->query->getInt('limit', 100)
	    );	    

	    return $pagination;
    }
}