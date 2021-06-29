<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Form\AddFavoriteType;
use App\Repository\FavoriteRepository;
use App\Repository\UserRepository;
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
        $favorite->setUser($this->getUser());
        $form = $this->createForm(AddFavoriteType::class, $favorite);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $favorite->getCategory()->setName(strtolower($favorite->getCategory()->getName()));
            $category = $this->getDoctrine()
                ->getRepository('App:Category')
                ->findOneBy(['name' => $favorite->getCategory()->getName()]);
            if($category) {
                $favorite->setCategory($category);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($favorite);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/addFavorite.html.twig',[
            'addFavoriteForm'=>$form->createView(),
            'categories' => $this->getFavorites()
        ]);
    }
    #[Route('/dashboard/list', name: 'listFavorite')]
    public function listFavorites(FavoriteRepository $favoriteRepository): Response
    {
        $favorites = $favoriteRepository->findBy(['user'=>$this->getUser()]);
        return $this->render('dashboard/list.html.twig',[
            'favorites'=> $favorites,
            'categories' => $this->getCategories()
        ]);
    }

    private function getFavorites(): array
    {
        $favoriteRepository = $this->getDoctrine()->getRepository('App:Favorite');
        $userRepository = $this->getDoctrine()->getRepository('App:User');
        if (!empty($this->getUser())) {
            $user = $userRepository->findOneBy(['email'=> $this->getUser()->getUserIdentifier()]);
            return $favoriteRepository->findBy(['user' => $user->getId()]);
        } else {
            return [];
        }
    }
    private function getCategories(): array
    {
        $categories = array();
        if(!empty($this->getFavorites())) {
            foreach ($this->getFavorites() as $favorite) {
                if (!in_array($favorite->getCategory(),$categories)) {
                    $categories[] = $favorite->getCategory();
                }
            }
        }
        sort($categories);
        return $categories;
    }
}
