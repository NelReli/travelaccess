<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    
    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('POST_NEW');
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setAuthor($this->getUser());
            $article->setCreatedAt(new \DateTimeImmutable());

            // Gestion des images
                $images = $form->get('images')->getData(); // Champ "images" dans le form

                foreach ($images as $imageFile) {
                    // Générer un nom unique
                    $safeArticleSlug = $slugger->slug($article->getSlug()); 
                    $newFilename = $safeArticleSlug . '-' . uniqid() . '.' . $imageFile->guessExtension();
                    try {
                    // Déplacer le fichier dans le dossier configuré
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    } catch (FileException $e) {
                        $this->addFlash('danger', "Une erreur est survenue lors de l'upload de l'image");
                    }

                    // Créer une entité Image
                    $image = new Image();
                    $image->setName($newFilename);
                    $article->addImage($image); // c'est cette méthode qui lie les deux et persist modif
                }

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès !');

            return $this->redirectToRoute('app_article_show', [
                'slug' => $article->getSlug(),
                'id' => $article->getId(),
            ], Response::HTTP_SEE_OTHER);

        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }


    #[Route('/{slug}-{id}', name: 'app_article_show', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG,'id' => Requirement::DIGITS])]
    public function show(Article $article, EntityManagerInterface $em, Request $request, CommentRepository $commentRepository): Response
    {
        // création nombre de vue
        $session = $request->getSession(); // éviter les multiples incréments avec session ouverte
        $viewedKey = 'viewed_article_' . $article->getId();

        if (!$session->has($viewedKey)) {
            $article->incrementViews();
            $em->flush();
            $session->set($viewedKey, true);
        }


        // création nouveau commentaire
        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $comment->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('POST_NEW', null);
            $article->addComment($comment);
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Commentaire ajouté !');
            
            return $this->redirectToRoute('app_article_show', [
                'slug' => $article->getSlug(),
                'id' => $article->getId(),
            ]);
        }

        $commentCounts = $commentRepository->commentCountByArticleId($article->getId());
        
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'comment_form' => $form->createView(),
            'commentCounts' => $commentCounts
        ]);
    }


    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('POST_EDIT', $article);
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();

                if (!empty($images)) {
                    foreach ($images as $imageFile) {
                        // Générer un nom unique
                        $safeArticleSlug = $slugger->slug($article->getSlug()); 
                        $newFilename = $safeArticleSlug . '-' . uniqid() . '.' . $imageFile->guessExtension();

                        try {
                        // Déplacer les images dans le dossier des uploads: articles
                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $newFilename
                        );
                        } catch (FileException $e) {
                            $this->addFlash('danger', "Une erreur est survenue lors de l'upload de l'image");
                        }

                        // Créer une entité Image
                        $image = new Image();
                        $image->setName($newFilename);
                        $article->addImage($image); // c'est cette méthode qui lie les deux et persist modif
                    }
                }

            $entityManager->flush();
            $this->addFlash('success', 'Article mis à jour avec succès !');
            return $this->redirectToRoute('app_profile_show', ['username' => $article->getAuthor()->getUsername()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('POST_DELETE', $article);
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article supprimé avec succès !');
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }



}
