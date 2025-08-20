<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchForm;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    // #[Route('/', name: 'app_home')]
    // public function index(ArticleRepository $articleRepository, CommentRepository $commentRepository): Response
    // {
    //     $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);
    //     $commentCounts = $commentRepository->commentCountByArticleId();
    //     return $this->render('home/index.html.twig', [
    //         'controller_name' => 'HomeController',
    //         'articles' => $articles,
    //         'commentCounts' => $commentCounts
    //     ]);
    // }

    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository, CommentRepository $commentRepository, Request $request): Response
    {
        $search = new SearchData(); // nouvel objet pour la recherche
        $form = $this->createForm(SearchForm::class, $search);
        $form->handleRequest($request);

        // Récupération de la page active
        $page = $request->query->getInt('page', 1);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Recherche + tri via repository
            $articles = $articleRepository->findSearch($search, $page);
        } else {
            $articles = $articleRepository->paginateArticles($page);
        }

        $commentCounts = $commentRepository->commentCountByArticleId();

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
            'commentCounts' => $commentCounts,
            'form' => $form->createView()
        ]);
    }

    #[Route('/profile/{username}', name: 'app_profile_show')]
    public function show(UserRepository $userRepository, string $username): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        $articles = $user->getArticles();
        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'articles' => $articles,
        ]);
    }
}
