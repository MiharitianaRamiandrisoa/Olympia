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
