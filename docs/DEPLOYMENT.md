# Procédure de déploiement Olympia

Le CI/CD transfère uniquement les sources par FTP. Composer et les migrations sont exécutés sur le serveur via SSH, afin de ne pas transférer `vendor`, `node_modules` ou les assets compilés par FTP.

## Préproduction

Chaque push sur `Dev` (ou `dev`) déploie automatiquement vers `https://test.olympia-madagascar.mg` et utilise une base de données séparée.

Le job de préproduction attend les secrets GitHub suivants :

- `PREPROD_APP_SECRET`
- `PREPROD_DB_NAME`
- `PREPROD_DB_USER`
- `PREPROD_DB_PASSWORD`
- `PREPROD_DB_HOST` (facultatif, `localhost` par défaut)
- `PREPROD_DB_SERVER_VERSION` (facultatif, `10.11.19-MariaDB` par défaut)
- `PREPROD_FTP_HOST` (facultatif si le serveur est identique à la production), `PREPROD_FTP_USER`, `PREPROD_FTP_PASSWORD` (obligatoires pour le compte FTP du sous-domaine)
- `PREPROD_SSH_SERVER_DIR` (facultatif, `/home/olympiam/domains/test.olympia-madagascar.mg/public_html` par défaut)
- `PREPROD_FTP_SERVER_DIR` (facultatif, `/public_html/` lorsque le compte FTP est positionné dans le dossier du sous-domaine)

Les identifiants SSH de production sont réutilisés par défaut. Le serveur FTP de production peut être réutilisé via `FTP_HOST`, mais le login et le mot de passe préproduction restent obligatoires afin d'éviter tout envoi avec le compte de production.

Le serveur SSH de préproduction doit disposer de PHP 8.2 ou supérieur et de Composer, comme le serveur de production. Les assets peuvent être compilés séparément sur le serveur avec npm si nécessaire.

Le mot de passe de la base ne doit pas être ajouté dans `.env`, dans le dépôt ou dans le workflow. Il doit uniquement être enregistré dans le secret GitHub `PREPROD_DB_PASSWORD`.

## 1. Transférer les sources

```bash
git add .
git commit -m "Mise à jour du site"
git push origin main
```

Attendre la fin du workflow `Deploy Olympia` dans GitHub Actions.

Pour la préproduction, le workflow exécute le même processus que la production : `composer install`, la compilation Tailwind, l'installation de l'ImportMap, les migrations et le nettoyage du cache directement sur le serveur SSH.

## 2. Compiler manuellement sur le serveur de production

```bash
ssh olympiam@web1.simafri.cloud
cd /home/olympiam/domains/olympia-madagascar.mg/public_html

# Le dossier public/build est ignoré par Git : il faut le reconstruire après chaque déploiement.
npm ci --include=dev
npm run build

php bin/console assets:install public --env=prod --no-interaction

export APP_ENV=prod
export APP_DEBUG=0

php bin/console tailwind:build --env=prod --no-interaction

rm -rf public/assets

php bin/console importmap:install --env=prod --no-interaction
php bin/console assets:install public --env=prod --no-interaction
php bin/console asset-map:compile --env=prod --no-interaction

mkdir -p public/assets/vendor/hotwired
cp -R public/assets/vendor/@hotwired/. public/assets/vendor/hotwired/

mkdir -p public/assets/symfony
cp -R public/assets/@symfony/. public/assets/symfony/

sed -i \
  -e 's#/assets/vendor/@hotwired/#/assets/vendor/hotwired/#g' \
  -e 's#/assets/@symfony/#/assets/symfony/#g' \
  public/assets/importmap.json

  find public -type d -exec chmod 755 {} +
find public -type f -exec chmod 644 {} +
php bin/console cache:clear --env=prod
```

## 3. Activer la billetterie

La migration `Version20261014150000` ajoute les champs de reservation a `evenement` et les tables `reservation`, `billet` et `controle_entree`. Apres sauvegarde de la base, l executer sur l environnement cible :

```bash
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
```

Pour envoyer les confirmations, remplacer `MAILER_DSN=null://null` par un DSN SMTP fonctionnel dans la configuration d environnement de production. Tant que cette valeur reste `null://null`, les reservations gratuites sont confirmees et les billets restent consultables via leur lien securise, mais aucun e-mail n est declare comme envoye.

Pour les evenements payants, le projet fournit une page de paiement simulee pour les tests. Apres le formulaire, la reservation est conservee pendant `PAYMENT_HOLD_MINUTES` minutes (15 par defaut), puis le paiement simule confirme la reservation et genere un billet par place. Une reservation non payee est supprimee a expiration. La commande `php bin/console app:reservations:cleanup-expired` doit etre executee regulierement par une tache planifiee en production.

Les utilisateurs charges du controle doivent recevoir le role `ROLE_TICKET_CONTROL`. Un `ROLE_ADMIN` herite automatiquement de ce role. L interface de controle est disponible sur `/billetterie/controle` et l export CSV sur `/olympia-admin/billetterie/participants.csv`.
