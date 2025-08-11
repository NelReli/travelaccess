<?php

namespace App\Twig;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private UserRepository $userRepository;
    private ArticleRepository $articleRepository;

    public function __construct(UserRepository $userRepository, ArticleRepository $articleRepository )
    {
        $this->userRepository = $userRepository;
        $this->articleRepository = $articleRepository;
    }

    public function getGlobals(): array
    {
        return [
            'userCount' => $this->userRepository->count([]),
            'articleCount' => $this->articleRepository->count([]),
            'cityCount' => $this->articleRepository->countDistinctCities([]),
        ];
    }
}
