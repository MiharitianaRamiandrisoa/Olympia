<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ReservationRequest
{
    #[Assert\NotBlank(message: 'Votre nom est obligatoire.')]
    #[Assert\Length(max: 160)]
    public string $nom = '';

    #[Assert\NotBlank(message: 'Votre adresse e-mail est obligatoire.')]
    #[Assert\Email(message: 'Saisissez une adresse e-mail valide.')]
    public string $email = '';

    #[Assert\Length(max: 40)]
    public ?string $telephone = null;

    #[Assert\Positive(message: 'Le nombre de places doit etre superieur a zero.')]
    #[Assert\LessThanOrEqual(value: 20, message: 'Vous pouvez reserver au maximum 20 places.')]
    public int $nombrePlaces = 1;
}
