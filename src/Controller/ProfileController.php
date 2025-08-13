<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileForm;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }
    
    #[Route('/profile/{username}', name: 'app_profile_show')]
    public function show(UserRepository $userRepository, ArticleRepository $articleRepository, string $username, CommentRepository $commentRepository): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        
        // Récupère les articles liés à l'utilisateur, triés par date de création DESC
        $articles = $articleRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );

        $commentCounts = $commentRepository->commentCountByArticleId();

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'articles' => $articles,
            'commentCounts' => $commentCounts
        ]);
    }

    #[Route('/profile/{username}/modifier', name: 'app_profile_edit')]
    public function edit(Request $request, UserRepository $userRepository, User $user, EntityManagerInterface $em, string $username): Response
    {
        // récupérer le user
        $user = $userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        
        // verification que c\'est le bon user
        $this->denyAccessUnlessGranted('POST_EDIT', $user);

        $form = $this->createForm(ProfileForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $newFilename = uniqid().'.'.$avatarFile->guessExtension();

                // Déplace le fichier dans le dossier avatars
                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de l\'image');
                    return $this->redirectToRoute('app_register');
                }

                $user->setAvatar($newFilename);
            }
            
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour !');
            return $this->redirectToRoute('app_profile_show', ['username' => $user->getUsername()]);
        }
        return $this->render('profile/edit.html.twig', ['form' => $form->createView()]);
    }

    
}