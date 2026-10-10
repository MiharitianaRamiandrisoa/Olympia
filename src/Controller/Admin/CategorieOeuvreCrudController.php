<?php

namespace App\Controller\Admin;

use App\Entity\CategorieOeuvre;

final class CategorieOeuvreCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = CategorieOeuvre::class;
}
