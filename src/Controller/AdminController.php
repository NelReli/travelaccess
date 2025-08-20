<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin')]
#[IsGranted("ROLE_ADMIN")]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        UserRepository $userRepo,
        ArticleRepository $articleRepo,
        CommentRepository $commentRepo,
    ): Response {
        $lastArticles = $articleRepo->findLastArticles();
        $lastComments = $commentRepo->findLastComments();
        $lastUsers = $userRepo->findLastUsers();

        return $this->render('admin/dashboard.html.twig', [
            // 'userCount' => $userRepo->count([]),
            // 'articleCount' => $articleRepo->count([]),
            'commentCount' => $commentRepo->count([]),
            'lastArticles' => $lastArticles,
            'lastComments' => $lastComments,
            'lastUsers' => $lastUsers,
        ]);
    }


    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        $usersWithCounts = $userRepo->findUsersWithArticleCount(true);

        return $this->render('admin/users.html.twig', [
            'usersWithCounts' => $usersWithCounts,
        ]);
    }

    #[Route('/users/delete/{id}', name: 'admin_delete_user')]
    public function deleteUser($id, UserRepository $userRepo, EntityManagerInterface $em): Response {

        $user = $userRepo->find($id);
        if ($user) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('warning', 'Utilisateur introuvable.');
        }
        return $this->redirectToRoute('admin_users');
    }


    #[Route('/articles', name: 'admin_articles')]
    public function articles(ArticleRepository $articleRepo): Response {
        return $this->render('admin/articles.html.twig', [
            'articles' => $articleRepo->findAll(),
        ]);
    }

    #[Route('/articles/delete/{id}', name: 'admin_delete_article')]
    public function deleteArticle($id, ArticleRepository $articleRepo, EntityManagerInterface $em): Response {

        $article = $articleRepo->find($id);
        if ($article) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'Article supprimé avec succès.');
        } else {
            $this->addFlash('warning', 'Article introuvable.');
        }
        return $this->redirectToRoute('admin_articles');
    }


    #[Route('/comments', name: 'admin_comments')]
    public function comments(CommentRepository $commentRepo): Response {
        return $this->render('admin/comments.html.twig', [
            'comments' => $commentRepo->findAll(),
        ]);
    }


    #[Route('/comments/delete/{id}', name: 'admin_delete_comment')]
    public function deleteComment($id, CommentRepository $commentRepo, EntityManagerInterface $em): Response {

        $comment = $commentRepo->find($id);
        if ($comment) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Commentaire supprimé avec succès.');
        } else {
            $this->addFlash('warning', 'Commentaire introuvable.');
        }
        return $this->redirectToRoute('admin_comments');
    }
}
