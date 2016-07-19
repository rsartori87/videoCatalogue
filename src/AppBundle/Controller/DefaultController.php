<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Video;
use AppBundle\Form\SearchFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Stomp\Client;
use Stomp\StatefulStomp;
use Stomp\Transport\Message;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        /*
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
        */
        return $this->render('video/index.html.twig');
    }

    /**
     * @Route("/data")
     * @Method("POST")
     */
    public function dataAction(Request $request)
    {
        $query = "*:*";
        $client = $this->get('solarium.client');
        $select = $client->createSelect();
        $start = 0;
        if ($request->getContent() != '') {
            $parameters = json_decode($request->getContent());
            if ($parameters->{'key'} != '') {
                $query = "path:" .$parameters->{'key'};
            }
            $start = $parameters->{'start'};
        }
        $select->setStart($start);
        $select->setQuery($query)->setRows(40);
        $select->addSort('modified_date', $select::SORT_DESC);
        $files = $client->select($select);
        $serializer = $this->get('serializer');
        $temp = ['data' => $files];
        $data = $serializer->serialize($temp, 'json');
        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/update")
     */
    public function updateAction(Request $request)
    {
        $finder = new Finder();
        $finder->files()->in($this->getParameter('video_path'))->name('/\.mp4$/');
        $stomp = new StatefulStomp(new Client('tcp://localhost:61613'));
        foreach ($finder as $file) {
            $msg = [
                'path' => $file->getRealPath(),
                'filename' => $file->getFilename(),
                'number' => md5($file->getFilename()),
                'modifiedDate' => $file->getMTime()
            ];
            //$this->get('old_sound_rabbit_mq.upload_directory_producer')->publish(json_encode($msg));
            $stomp->send('/queue/demo', new Message(json_encode($msg)));
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
