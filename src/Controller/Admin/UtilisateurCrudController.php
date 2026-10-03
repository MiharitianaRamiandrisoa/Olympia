<?php
namespace App\Controller\Admin;
use App\Entity\Utilisateur;
final class UtilisateurCrudController extends AbstractEntityCrudController { protected static string $entityClass = Utilisateur::class; }
