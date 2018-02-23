<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Employee;
use AppBundle\Entity\Position;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends Controller
{
	/**
	 * @Route("/admin", name="admin_page")
	 */
	public function adminAction(Request $request)
	{   
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
			return new Response("kek");
	}

	/**
	 * @Route("/", name="homepage")
	 */
	public function indexAction(Request $request)
	{      
		$employeesArr = $this->getEmployees();
		// if (!$employeesArr) {
		// 	throw $this->createNotFoundException(
		// 	'No employees found!');
		// }        

		return $this->render('default/main.html.twig', [
			'employees_list' => $employeesArr
		]);
	}

	/**
	 * @Route("/employees", name="employees_list")
	 */
	public function showEmployeesListAction(Request $request)
	{
		if (isset($_POST['sort']) && isset($_POST['order'])) {
			$orderBy = $_POST['order'];
			$sortBy = $_POST['sort'];
			$employeeRepository = $this->getDoctrine()->getRepository('AppBundle:Employee');
			// Sort search results
			if (isset($_POST['searchWord']) && !empty($_POST['searchWord'])) {
				$em = $this->getDoctrine()->getManager();			
				$query = $em->createQuery("SELECT e FROM AppBundle:Employee e 
				JOIN AppBundle:Position p 
				WITH e.positionId = p.id 
				WHERE e.fullName LIKE :word
				OR p.name LIKE :word 
				OR e.salary LIKE :word
				OR e.recruitingDate LIKE :word
				OR e.id LIKE :word 
				ORDER BY e.$orderBy $sortBy")
				->setParameter('word', '%'.$_POST['searchWord'].'%');
				$employeesArr = $query->getResult();
			} else {
				// Sort all records
				$employeesArr = $employeeRepository->findBy(array(), array($orderBy => $sortBy));
			}
			// if (!$employeesArr) {
			// 	throw $this->createNotFoundException(
			// 	'No employees found!');
			// }		
			if ($sortBy == 'asc') {
				$sortBy = 'desc';
			} else {
				$sortBy = 'asc';
			}
			$template = $this->get('twig')
				->loadTemplate("default/employees.html.twig");
			$newTable = $template->renderBlock("table", array(
				'employees_list' => $employeesArr,
				'sortBy' => $sortBy));

			return new Response(
				json_encode(array(
					'newTable' => $newTable
				)
			));
		} elseif (isset($_POST['searchWord']) && strlen($_POST['searchWord']) > 0) {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery("SELECT e FROM AppBundle:Employee e 
				JOIN AppBundle:Position p 
				WITH e.positionId = p.id 
				WHERE e.fullName LIKE :word
				OR p.name LIKE :word 
				OR e.salary LIKE :word
				OR e.recruitingDate LIKE :word
				OR e.id LIKE :word")
				->setParameter('word', '%'.$_POST['searchWord'].'%');

			$employeesArr = $query->getResult();
			if (sizeof($employeesArr) == 0) {
				$msg = 'No results by your search word!';
			} else {
				$template = $this->get('twig')
					->loadTemplate("default/employees.html.twig");
				$newTable = $template->renderBlock("table", array(
					'employees_list' => $employeesArr));
			}
			return new Response (
				json_encode(array(
					'newTable' => isset($newTable) ? $newTable : false,
					'msg' => isset($msg) ? $msg : ''
				)
			));
																																													
		}

		$positionsArr = $this->getDoctrine()->getRepository('AppBundle:Position')->findAll();
		// Is it necessary? Maybe only in Twig?
		if (!$positionsArr) {
			throw $this->createNotFoundException(
			'No positions found!');
		}
		$employeesArr = $this->getEmployees();
		
		return $this->render('default/employees.html.twig', [
			'employees_list' => $employeesArr,
			'positions_list' => $positionsArr,
			'sortBy' => 'asc',
		]);
	}

	/**
	 * @Route("/update-employee", name="employee_update")
	 */
	public function updateEmployeeAction(Request $request)
	{
		$employeeId = isset($_POST['employeeId']) ? $_POST['employeeId'] : false;
		$fullName = isset($_POST['fullName']) ? $_POST['fullName'] : false;
		$positionId = isset($_POST['positionId']) ? $_POST['positionId'] : false;
		$salary = isset($_POST['salary']) ? $_POST['salary'] : false;
		$parentId = isset($_POST['parentId']) ? $_POST['parentId'] : false;
		$recruitingDate = isset($_POST['recruitingDate']) ? $_POST['recruitingDate'] : false;

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

	// Придумати алгоритм побудови дерева, щоби кожен співробітник явно був під своїм начальником
	// Тобто замінити систему level на чітку побудову "батько-син" - таби додавати не по рівню, а по ієрархії

			// $error = $recruitingDate;

			if ($recruitingDate != false && $this->isRealDate($recruitingDate)) {
				$employee->setRecruitingDate(new \DateTime($recruitingDate));
			} else {
				$error .= "Uncorrect date!\n";
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
		}

		$employeesArr = $employeeRepository->findAll();
		$template = $this->get('twig')
			->loadTemplate("default/employees.html.twig");
		$newTable = $template->renderBlock("table", array(
			'employees_list' => $employeesArr));

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
		$em = $this->getDoctrine()->getManager();
		$employeeRepository = $em->getRepository('AppBundle:Employee');
		
		// Add first CEO if no employees yet
		$fullName = isset($_POST['fullName']) ? $_POST['fullName'] : false;
		$positionId = isset($_POST['positionId']) ? $_POST['positionId'] : false;
		$salary = isset($_POST['salary']) ? $_POST['salary'] : false;
		$recruitingDate = isset($_POST['recruitingDate']) ? $_POST['recruitingDate'] : false;
		$parentId = isset($_POST['parentId']) ? $_POST['parentId'] : false;
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
			if ($lastId && $parentEmployee) {
				$pk = $lastId[0]['id'] + 1;
			} else {
				$pk = 1;
			}

			$employee = new Employee();
			$employee->setId($pk);
			$employee->setFullName($fullName);
			$employee->setPositionId($em->getReference('AppBundle:Position', $position->getId()));
			$employee->setRecruitingDate(new \DateTime($recruitingDate));
			$employee->setParentId($parent);
			$employee->setSalary($salary);
			$em->persist($employee);
			$em->flush();
		} else {
			$error = 'Some error? -_-';
		}

		$employeesArr = $employeeRepository->findAll();
		$template = $this->get('twig')
			->loadTemplate("default/employees.html.twig");
		$newTable = $template->renderBlock("table", array(
			'employees_list' => $employeesArr));

		return new Response (
			json_encode(array(
				'error' => isset($error) ? $error : false,
				'newTable' => isset($newTable) ? $newTable : false
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
			$em->remove($employee);
			$em->flush();
		}

		$this->addFlash(
            'success',
            'User was successfully deleted'
        );

        return $this->redirectToRoute('employees_list');
	}

	public function getEmployees()
	{
		$employees = $this->getDoctrine()->getRepository('AppBundle:Employee')->findAll();

		return $employees;
	}

	public function isRealDate($date) { 
	    if (false === strtotime($date)) { 
	        return false;
	    } 
	    list($year, $month, $day) = explode('-', $date); 
	 
	    return checkdate($month, $day, $year);
	}

}
