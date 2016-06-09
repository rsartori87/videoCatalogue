<?php
/**
 * Created by PhpStorm.
 * User: sonic
 * Date: 07/06/16
 * Time: 12.55
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenicationUtils = $this->get('security.authentication_utils');

        $error = $authenicationUtils->getLastAuthenticationError();

        $lastUsername = $authenicationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }
}