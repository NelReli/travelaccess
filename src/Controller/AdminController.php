<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;;


#[Route('/admin')]
#[IsGranted("ROLE_ADMIN")]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(UserRepository $userRepo, ArticleRepository $articleRepo, CommentRepository $commentRepo,): Response 
    {
        $lastArticles = $articleRepo->findLastArticles();
        $lastComments = $commentRepo->findLastComments();
        $lastUsers = $userRepo->findLastUsers();

        return $this->render('admin/dashboard.html.twig', [
            'commentCount' => $commentRepo->count([]),
            'lastArticles' => $lastArticles,
            'lastComments' => $lastComments,
            'lastUsers' => $lastUsers,
        ]);
    }

    //routes pour gerer les users
    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        $usersWithCounts = $userRepo->findUsersWithArticleCount();

        return $this->render('admin/users.html.twig', [
            'usersWithCounts' => $usersWithCounts,
        ]);
    }

    #[Route('/users/{id}', name: 'admin_delete_user', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $em): Response 
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
            // Empêcher l'admin de se supprimer lui-même
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
                return $this->redirectToRoute('admin_users', [], Response::HTTP_SEE_OTHER);
            }

            // Supprimer l'avatar de l'utilisateur
            if ($user->getAvatar()) {
                $avatarPath = $this->getParameter('avatars_directory') . '/' . $user->getAvatar();
                
                if (file_exists($avatarPath)) {
                    unlink($avatarPath);
                }
            }

            $em->remove($user);
            $em->flush();
            
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }
        
        return $this->redirectToRoute('admin_users', [], Response::HTTP_SEE_OTHER);
    }

    //routes pour gerer les articles
    #[Route('/articles', name: 'admin_articles')]
    public function articles(ArticleRepository $articleRepo): Response {
        return $this->render('admin/articles.html.twig', [
            'articles' => $articleRepo->findAll(),
        ]);
    }

    #[Route('/articles/{id}', name: 'admin_delete_article', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function deleteArticle(Article $article, Request $request, EntityManagerInterface $em): Response 
    {
        if ($this->isCsrfTokenValid('delete_article_' . $article->getId(), $request->request->get('_token'))) {
            // Supprimer toutes les images de l'article
            $images = $article->getImages()->toArray();
            
            foreach ($images as $image) {
                $imagePath = $this->getParameter('images_directory') . '/' . $image->getName();
                
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $em->remove($image);
            }

            $em->remove($article);
            $em->flush();
            
            $this->addFlash('success', 'Article et ses images supprimés avec succès.');
        }
        
        return $this->redirectToRoute('admin_articles', [], Response::HTTP_SEE_OTHER);
    }

    //routes pour gerer les comments
    #[Route('/comments', name: 'admin_comments')]
    public function comments(CommentRepository $commentRepo): Response {
        return $this->render('admin/comments.html.twig', [
            'comments' => $commentRepo->findAll(),
        ]);
    }


    #[Route('/comments/{id}', name: 'admin_delete_comment', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function deleteComment(Comment $comment, Request $request, EntityManagerInterface $em): Response 
    {
        if ($this->isCsrfTokenValid('delete_comment_' . $comment->getId(), $request->request->get('_token'))) {
            $em->remove($comment);
            $em->flush();
            
            $this->addFlash('success', 'Commentaire supprimé avec succès.');
        }
        
        return $this->redirectToRoute('admin_comments', [], Response::HTTP_SEE_OTHER);
    }


}
