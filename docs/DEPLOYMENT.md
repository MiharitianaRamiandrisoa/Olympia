# Déploiement CI/CD Olympia

Le workflow `.github/workflows/ci-cd.yml` effectue les opérations suivantes :

1. installe PHP, Composer, Node.js et MySQL 8.4 pour la CI ;
2. valide Composer, le conteneur Symfony et les templates Twig ;
3. compile Tailwind et AssetMapper ;
4. exécute PHPUnit ;
5. après un push sur `main`, transfère le projet par SSH/rsync ;
6. installe les dépendances et reconstruit le cache en production ;
7. vérifie l’URL publique du site.

## Secrets GitHub à créer

Dans GitHub : `Settings > Secrets and variables > Actions`, créer les secrets suivants :

- `SERVER_HOST` : nom DNS ou adresse IP du serveur ;
- `SERVER_USER` : utilisateur Linux dédié au déploiement ;
- `SERVER_PATH` : chemin absolu de l’application, par exemple `/var/www/olympia` ;
- `SERVER_SSH_KEY` : clé privée SSH du déploiement ;
- `APP_URL` : URL publique, par exemple `https://olympia.example.com`.

Créer aussi un environnement GitHub nommé `production` et, si nécessaire, activer une validation manuelle avant le job `deploy`.

## Configuration obligatoire sur le serveur

Le fichier `.env.local` doit être créé directement sur le serveur et ne doit pas être remplacé par le workflow :

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=une-cle-secrete-unique
DATABASE_URL="mysql://utilisateur:mot_de_passe@127.0.0.1:3306/olympia?serverVersion=8.4.0&charset=utf8mb4"
```

Le serveur web doit pointer vers le dossier `public/`, et l’utilisateur PHP doit pouvoir écrire dans `var/` et `public/uploads/`.

## Base de données

Le workflow ne lance volontairement pas `doctrine:schema:update --force` et ne modifie pas automatiquement la base de production.

Avant le premier déploiement, vérifier que toutes les tables applicatives sont déjà présentes. Le dossier `migrations/` doit ensuite être complété avec de vraies migrations avant d’automatiser les évolutions de schéma.

## Premier lancement

Après avoir ajouté les secrets, faire un petit commit puis pousser sur `main` :

```bash
git add .github/workflows/ci-cd.yml docs/DEPLOYMENT.md
git commit -m "Add CI/CD deployment pipeline"
git push origin main
```

Le job `quality` doit réussir avant que le job `deploy` soit autorisé à s’exécuter.
