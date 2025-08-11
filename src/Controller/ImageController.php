<?php

namespace App\Controller;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/image')]
final class ImageController extends AbstractController
{

    #[Route('/{id}/delete', name: 'app_image_delete', methods: ['POST'])]
    public function deleteImage(Request $request, Image $image, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $imagePath = $this->getParameter('images_directory') . '/' . $image->getName();
            if (file_exists($imagePath)) {
                unlink($imagePath); // supprime le fichier physique
            }

            $em->remove($image);
            $em->flush();

            $this->addFlash('success', 'Image supprimée.');
        }

        return $this->redirectToRoute('app_article_edit', ['id' => $image->getArticle()->getId()]);
    }
}
