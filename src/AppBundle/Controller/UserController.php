<?php
/**
 * Created by PhpStorm.
 * User: sonic
 * Date: 10/06/16
 * Time: 16.12
 */

namespace AppBundle\Controller;


use AppBundle\Form\UserFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/user/edit", name="edit_user")
     */
    public function editAction(Request $request)
    {
        $usrId = $this->getUser()->getId();
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($usrId);
        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword(password_hash($user->getPassword(), PASSWORD_BCRYPT, ['cost' => 12]));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Dati utente modificati');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/edit.html.twig', [
            'userForm' => $form->createView()
        ]);
    }
}