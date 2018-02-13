<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;

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
        $employeesArr = $this->getEmployees();
        if (!$employeesArr) {
            throw $this->createNotFoundException(
            'No employees found!');
        }

        return $this->render('default/employees.html.twig', [
            'employees_list' => $employeesArr
        ]);
    }

    public function getEmployees()
    {
        $employees = $this->getDoctrine()->getRepository('AppBundle:Employee')->findAll();

        return $employees;
    }

}
