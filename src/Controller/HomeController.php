<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository, CommentRepository $commentRepository): Response
    {
        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);
        $commentCounts = $commentRepository->commentCountByArticleId();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'articles' => $articles,
            'commentCounts' => $commentCounts
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
