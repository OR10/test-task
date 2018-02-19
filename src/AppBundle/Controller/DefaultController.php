<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Employee;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
	/**
	 * @Route("/", name="homepage")
	 */
	public function indexAction(Request $request)
	{      
		$employeesArr = $this->getEmployees();
		if (!$employeesArr) {
			throw $this->createNotFoundException(
			'No employees found!');
		}        

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
			$repository = $this->getDoctrine()->getRepository('AppBundle:Employee');
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
				$employeesArr = $repository->findBy(array(), array($orderBy => $sortBy));
			}
			if (!$employeesArr) {
				throw $this->createNotFoundException(
				'No employees found!');
			}		
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

		$employeesArr = $this->getEmployees();
		if (!$employeesArr) {
			throw $this->createNotFoundException(
			'No employees found!');
		}
		
		return $this->render('default/employees.html.twig', [
			'employees_list' => $employeesArr,
			'sortBy' => 'asc',
		]);
	}

	public function getEmployees()
	{
		$employees = $this->getDoctrine()->getRepository('AppBundle:Employee')->findAll();

		return $employees;
	}

}
