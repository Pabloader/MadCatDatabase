<?php

namespace Pabloid\MCDBundle\Controller;

use Pabloid\MCDBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $builder = $this->createFormBuilder([]);
        $builder
            ->add('file', 'file')
            ->add('submit', 'submit');
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            if (substr($file->getMimeType(), 0, strlen('image/')) === 'image/') {
                $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $newName = substr(md5(time()), 0, 12);
                $file->move(realpath($this->getParameter('kernel.root_dir') . '/../web/uploads'), $newName . '.' . $ext);
                $image = new Image();
                $image->setFilename('/uploads/' . $newName . '.' . $ext);
                $em->persist($image);
                $em->flush();
            }
        }

        $images = $em->getRepository('PabloidMCDBundle:Image')->findAll();
        if (!empty($images)) {
            $rand = array_rand($images);
            $image = $images[$rand];
        } else {
            $image = null;
        }
        return ['image' => $image, 'form' => $form->createView()];
    }
}
