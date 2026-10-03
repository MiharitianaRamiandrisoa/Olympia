# Déploiement CI/CD Olympia

Le workflow `.github/workflows/ci-cd.yml` effectue les opérations suivantes :

1. installe PHP, Composer, Node.js et MySQL 8.4 pour la CI ;
2. valide Composer, le conteneur Symfony et les templates Twig ;
3. compile Tailwind et AssetMapper ;
4. exécute PHPUnit ;
5. après un push sur `main`, transfère le projet par FTP ;
6. installe les dépendances et reconstruit le cache en production ;
7. vérifie l’URL publique du site.

## Secrets GitHub à créer

Dans GitHub : `Settings > Secrets and variables > Actions`, créer les secrets suivants :

- `FTP_HOST` : hôte FTP fourni par l’hébergeur ;
- `FTP_USER` : identifiant FTP ;
- `FTP_PASSWORD` : mot de passe FTP ;
- `FTP_SERVER_DIR` : dossier distant de l’application, par exemple `/home/olympiam/domains/olympia-madagascar.mg/` ;
- `APP_URL` : URL publique, par exemple `https://olympia.example.com`.

Pour générer automatiquement `.env.local` pendant le déploiement, ajouter également :

- `APP_SECRET` : clé secrète longue et aléatoire pour Symfony ;
- `HOST_BASE` : hostname MySQL ;
- `NOM_BASE` : nom de la base MySQL ;
- `NOM_UTILISATEUR` : utilisateur MySQL ;
- `MOT_DE_PASSE` : mot de passe MySQL.

Créer aussi un environnement GitHub nommé `production` et, si nécessaire, activer une validation manuelle avant le job `deploy`.

## Configuration obligatoire sur le serveur

Le workflow génère `.env.local` à partir des secrets GitHub et le transfère par FTP. Il ne faut jamais ajouter ce fichier au dépôt :

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=une-cle-secrete-unique
DATABASE_URL="mysql://utilisateur:mot_de_passe@127.0.0.1:3306/olympia?serverVersion=8.4.0&charset=utf8mb4"
```

Le workflow force également `APP_ENV=prod` et `APP_DEBUG=0` pendant les commandes de déploiement. Cela évite que Composer tente de charger les bundles de développement absents de l’installation `--no-dev`.

Le serveur web doit pointer vers le dossier `public/`, et l’utilisateur PHP doit pouvoir écrire dans `var/` et `public/uploads/`.

Le déploiement utilise FTPS (FTP avec TLS), car l’hébergeur refuse les connexions FTP non chiffrées. Il ne peut pas exécuter de commandes sur le serveur : il faut donc vérifier depuis le panneau d’hébergement que Composer, le cache Symfony et les permissions sont correctement préparés. Pour un déploiement entièrement automatisé, un accès SSH/SFTP reste préférable.

## Base de données

Le workflow ne lance volontairement pas `doctrine:schema:update --force` et ne modifie pas automatiquement la base de production.

Les identifiants FTP, SSH et MySQL ne doivent pas être enregistrés dans Git, dans une capture d’écran ou dans le workflow. Ils doivent rester dans le fichier `.env.local` du serveur et dans les secrets GitHub (`FTP_PASSWORD`, `FTP_HOST`, etc.).

Avant le premier déploiement, vérifier que toutes les tables applicatives sont déjà présentes. Le dossier `migrations/` doit ensuite être complété avec de vraies migrations avant d’automatiser les évolutions de schéma.

## Premier lancement

Après avoir ajouté les secrets, faire un petit commit puis pousser sur `main` :

```bash
git add .github/workflows/ci-cd.yml docs/DEPLOYMENT.md
git commit -m "Add CI/CD deployment pipeline"
git push origin main
```

Le job `quality` doit réussir avant que le job `deploy` soit autorisé à s’exécuter.
