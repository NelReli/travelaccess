<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ContactDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255)]
    public string $name;
    
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;
    
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255)]
    public string $subject;
    
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, minMessage: "Le message doit faire au moins 10 caractères.")]
    public string $message;
}