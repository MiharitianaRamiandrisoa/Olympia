<?php

namespace App\Controller\Admin;

use App\Entity\Artiste;

final class ArtisteCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Artiste::class;
}
