<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Form\AddFavoriteType;
use App\Repository\FavoriteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard',name: 'dashboard_')]
class DashboardController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'username' => $this->getUser()->getUsername(),
            'categories' => $this->getCategories()
        ]);
    }

    #[Route('/add', name: 'add_favorite')]
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

            return $this->redirectToRoute('dashboard_index');
        }

        return $this->render('dashboard/addFavorite.html.twig',[
            'addFavoriteForm'=>$form->createView(),
            'categories' => $this->getCategories()
        ]);
    }
    #[Route('/list', name: 'list_favorites')]
    public function listFavorites(FavoriteRepository $favoriteRepository): Response
    {
        $favorites = $favoriteRepository->findBy(['user'=>$this->getUser()]);
        return $this->render('dashboard/list.html.twig',[
            'favorites'=> $favorites,
            'categories' => $this->getCategories()
        ]);
    }

    #[Route('/list-by/{sort}', name: 'list_favorites_by')]
    public function sort(string $sort):Response
    {
        $user = $this->getUser();
        if($sort==='category'){
            $favorites = $this->getDoctrine()
                ->getRepository('App:Favorite')
                ->findAllSortByCategory($user);
        } else {
            $favorites = $this->getDoctrine()
                ->getRepository('App:Favorite')
                ->findAllSortByName($user);
        }

        return $this->render('dashboard/list.html.twig',[
            'favorites'=>$favorites,
            'categories'=>$this->getCategories()
        ]);
    }
    #[Route('/category/{category}', name:'category')]
    public function listByCategory(string $category): Response
    {
        $category = $this->getDoctrine()
            ->getRepository('App:Category')
            ->findOneBy(['name'=>$category]);
        $favorites = $this->getDoctrine()
            ->getRepository('App:Favorite')
            ->findBy([
                'category'=>$category,
                'user'=>$this->getUser()
            ],
                ['name'=> 'Asc']
            );

        return $this->render('dashboard/list.html.twig',[
            'favorites'=>$favorites,
            'categories'=>$this->getCategories()
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_favorite')]
    public function deleteFavorite(Favorite $favorite, EntityManagerInterface $entityManager,Request $request):?Response
    {
        if ($favorite->getUser()->getEmail() === $this->getUser()->getUserIdentifier()) {
            $entityManager->remove($favorite);
            $entityManager->flush();
        } else {
            throw $this->createNotFoundException(
                'This favorite does not exist'
            );
        }

        return $this->redirect($this->previousUrl($request));

    }

    #[Route('/edit/{id}',name: 'edit_favorite')]
    public function editFavorite(Favorite $favorite, Request $request):Response
    {
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
            $em->flush();

            return $this->redirectToRoute('dashboard_list_favorites');
        }

        return $this->render('dashboard/addFavorite.html.twig',[
            'addFavoriteForm'=>$form->createView(),
            'categories' => $this->getCategories()
        ]);
    }

    private function getCategories(): array
    {
        $user = $this->getUser();
        $favorites = $this->getDoctrine()
            ->getRepository('App:Favorite')
            ->findAllSortByCategory($user);
        $categories = array();

        if(!empty($favorites)) {
            foreach ($favorites as $favorite) {
                if (!in_array($favorite->getCategory(),$categories)) {
                    $categories[] = $favorite->getCategory()->getName();
                }
            }
        }
        return $categories;
    }

    private function previousUrl($request):string
    {
        $referer = $request->headers->get('referer');
        if ($referer == NULL) {
            $url = $this->generateUrl('dashboard_index');
        } else {
            $url = $referer;
        }
        return $url;
    }
}
