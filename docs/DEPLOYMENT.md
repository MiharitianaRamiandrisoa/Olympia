# Procédure de déploiement Olympia

Le CI/CD transfère uniquement les sources. La compilation des assets est effectuée manuellement sur le serveur.

## 1. Transférer les sources

```bash
git add .
git commit -m "Mise à jour du site"
git push origin main
```

Attendre la fin du workflow `Deploy Olympia` dans GitHub Actions.

Le workflow n’exécute pas `npm run build`, `importmap:install` ou `asset-map:compile`.

## 2. Compiler manuellement sur le serveur

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
