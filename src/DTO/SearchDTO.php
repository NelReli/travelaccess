<?php

namespace App\DTO;

class SearchDTO
{
    /**
     * @var string|null Recherche par titre
     */
    public ?string $q = null;

    /**
     * @var string|null Recherche par ville
     */
    public ?string $city = null;

    /**
     * @var string|null Recherche par pays
     */
    public ?string $country = null;

    /**
     * @var string|null Tri sélectionné (views, comments, accessibility)
     */
    public ?string $order = null;
}
