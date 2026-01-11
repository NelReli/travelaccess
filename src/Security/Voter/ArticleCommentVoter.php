<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Article;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class ArticleCommentVoter extends Voter
{
    public const NEW = 'POST_NEW'; 
    public const EDIT = 'POST_EDIT';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::NEW) {
            return true;
        }

        return in_array($attribute, [self::EDIT, self::DELETE])
            && ($subject instanceof Article 
                || $subject instanceof Comment
                || $subject instanceof User
            );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Le user doit être connecté
        if (!$user instanceof User) {
            return false;
        }

        // Les admins ont tous les droits
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case self::NEW:
                return true;

            case self::EDIT:
            case self::DELETE:
                // édition de son propre profil
                if ($subject instanceof User) {
                    return $subject->getId() === $user->getId();
                }

                // vérifier l'auteur de l article ou comment
                if (method_exists($subject, 'getAuthor')) {
                    return $subject->getAuthor()->getId() === $user->getId();
                }

            return false;
        }

        return false;
    }
}

