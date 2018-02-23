<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
	/**
     * @Route("/login", name="login_page")
     */
	public function loginAction(Request $request, AuthenticationUtils $authUtils)
	{
	    $error = $authUtils->getLastAuthenticationError();
	    $lastUsername = $authUtils->getLastUsername();

	    return $this->render('default/login.html.twig', array(
	        'last_username' => $lastUsername,
	        'error'         => $error,
	    ));
	}

    /**
     * @Route("/register", name="register_page")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // Build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // Handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $username = $user->getUsername();
			$email = $user->getEmail();

			$em = $this->getDoctrine()->getManager();			
			$query = $em->createQuery("SELECT u.username, u.email FROM AppBundle:User u");
			$dataArr = $query->getResult();

			$error = '';
			if ($dataArr) {
				foreach ($dataArr as $value) {
					if ($username == $value['username']) {
						$error .= "Such username already exists!\n";
					}
					if ($email == $value['email']) {
						$error .= "Such email already exists!";
					}
				}
			}

			if ($error == '') {				
	            // Save the User
	            $em = $this->getDoctrine()->getManager();
	            $em->persist($user);
	            $em->flush();
			} else {
				return $this->render('default/register.html.twig', array(
			        'form' => $form->createView(),
			        'error' => htmlspecialchars($error),
			    ));
			}

            $this->addFlash(
	            'success',
	            'User was successfully registered'
	        );

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'default/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/logout", name="logout_page")
     */
	public function logoutAction(Request $request, AuthenticationUtils $authUtils)
	{
		return $this->redirectToRoute('homepage');
	}
}