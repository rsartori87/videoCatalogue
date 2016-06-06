<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Video;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $files = $this->getDoctrine()
            ->getRepository('AppBundle:Video')
            //->findBy(array(), array('id' => 'ASC'), 20);
            ->findAll();
        // replace this example code with whatever you need
        return $this->render('video/index.html.twig', [
            'files' => $files
        ]);
    }

    /**
     * @Route("/update")
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $finder = new Finder();
        $finder->files()->in('/home/sonic/lavoro/shared/Backup/')->name('/\.mp4$/');
        foreach ($finder as $file) {
            $video = new Video();
            $video->setPath($file->getRealPath());
            $video->setName($file->getFilename());
            $em->persist($video);
        }
        $em->flush();
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/video/{id}", name="video")
     */
    public function videoAction($id)
    {
        $file = $this->getDoctrine()
            ->getRepository('AppBundle:Video')
            ->find($id);
        return $this->render('video/player.html.twig', [
            'video' => $file
        ]);
    }

    /**
     * @Route("/play/{id}", name="play")
     */
    public function playAction($id)
    {
        $file = $this->getDoctrine()
            ->getRepository('AppBundle:Video')
            ->find($id);
        $response = new BinaryFileResponse($file->getPath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }
}
