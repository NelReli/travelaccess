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

    #[Route('/{id}/delete', name: 'app_image_delete', methods: ['DELETE'])]
    public function deleteImage(Request $request, Image $image, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('POST_EDIT', $image->getArticle());

        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $imagePath = $this->getParameter('images_directory') . '/' . $image->getName();
            if (file_exists($imagePath)) {
                //suppression du fichier physique
                try {
                    unlink($imagePath);
                } catch (\Exception $e) {
                    $this->addFlash('warning', "Impossible de supprimer le fichier physique : ".$e->getMessage());
                }
            }

            $em->remove($image);
            $em->flush();

            $this->addFlash('success', 'Image supprimée avec succès.');
        }

        return $this->redirectToRoute('app_article_edit', ['id' => $image->getArticle()->getId()]);
    }
}
