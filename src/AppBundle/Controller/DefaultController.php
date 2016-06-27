<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Video;
use AppBundle\Form\SearchFormType;
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
        $query = "*:*";
        $form = $this->createForm(SearchFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $query = "path:".$form->getData()['search'];
        }

        $client = $this->get('solarium.client');
        $select = $client->createSelect();
        $select->setQuery($query)->setRows(2000);
        $select->addSort('modified_date', $select::SORT_DESC);
        $files = $client->select($select);
        return $this->render('video/index.html.twig', [
            'files' => $files,
            'searchForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/update")
     */
    public function updateAction(Request $request)
    {
        $finder = new Finder();
        $finder->files()->in('/home/sonic/lavoro/shared/Backup/')->name('/\.mp4$/');
        foreach ($finder as $file) {
            $msg = [
                'path' => $file->getRealPath(),
                'filename' => $file->getFilename(),
                'number' => md5($file->getFilename()),
                'modifiedDate' => $file->getMTime()
            ];
            $this->get('old_sound_rabbit_mq.upload_directory_producer')->publish(json_encode($msg));
        }
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
