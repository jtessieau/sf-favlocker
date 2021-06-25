<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Form\AddFavoriteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{

    #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'username' => $this->getUser()->getUsername(),
        ]);
    }

    #[Route('/dashboard/add', name: 'addFavorite')]
    public function addFavorite(Request $request): Response
    {
        $favorite = new Favorite();
        $form = $this->createForm(AddFavoriteType::class, $favorite);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($favorite);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/addFavorite.html.twig',[
            'addFavoriteForm'=>$form->createView(),
        ]);
    }
}
