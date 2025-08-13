<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class ArticleCommentVoter extends Voter
{
    public const NEW = 'POST_NEW'; 
    public const EDIT = 'POST_EDIT';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // pas de verification de subject a la creation (n existe pas encore)
        if ($attribute === self::NEW) {
            return true;
        }

        return in_array($attribute, [self::EDIT, self::DELETE])
            && ($subject instanceof \App\Entity\Article 
                || $subject instanceof \App\Entity\Comment
                || $subject instanceof User
            );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Les admins ont tous les droits
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case self::NEW:
                // Tous les utilisateurs connectés peuvent créer un article ou commentaire
                return true;

            // Vérification et comparaison l’auteur de l’objet avec l’utilisateur connecté
            // que c'est bien l utilisateur connecte, ou que c'est bien l auteur 
            case self::EDIT:
            case self::DELETE:
                if ($subject instanceof User) {
                    return $subject === $user;
                }

                if (method_exists($subject, 'getAuthor')) {
                    return $subject->getAuthor() === $user;
                }

                return false;
        }

        return false;
    }
}
