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
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository, CommentRepository $commentRepository, Request $request): Response
    {
        $cookiesAccepted = $request->cookies->get('cookies_accepted') === 'true';
        $cookiesRefused = $request->cookies->get('cookies_refused') === 'true';

        $lastArticles = $articleRepository->findLastArticles();
        $mostViewArticles = $articleRepository->findMostViewArticles();
        $mostCommentArticles = $articleRepository->findMostCommentArticles();
        $search = new SearchData(); // nouvel objet pour la recherche
        $cities = $articleRepository->findAllCities();
        $countries = $articleRepository->findAllCountries();
        $form = $this->createForm(SearchForm::class, $search, [
            'cities' => $cities,
            'countries' => $countries,
        ]);
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
            'form' => $form->createView(),
            'lastArticles' => $lastArticles,
            'mostViewArticles' =>  $mostViewArticles,
            'mostCommentArticles' => $mostCommentArticles,
            'cookiesAccepted' =>$cookiesAccepted,
            'cookiesRefused' => $cookiesRefused
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
