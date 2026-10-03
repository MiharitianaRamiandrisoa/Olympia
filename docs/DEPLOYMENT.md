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

npm ci --no-audit --no-fund
npm run build

php bin/console importmap:install --env=prod --no-interaction
rm -rf public/assets
php bin/console asset-map:compile --env=prod --no-interaction
```

## 3. Éviter le blocage des dossiers `@`

Certains hébergements refusent les URLs contenant `@hotwired` ou `@symfony`. Exécuter :

```bash
if [ -d public/assets/vendor/@hotwired ]; then
  mkdir -p public/assets/vendor/hotwired
  cp -R public/assets/vendor/@hotwired/. public/assets/vendor/hotwired/
fi

if [ -d public/assets/@symfony ]; then
  mkdir -p public/assets/symfony
  cp -R public/assets/@symfony/. public/assets/symfony/
fi

sed -i \
  -e 's#/assets/vendor/@hotwired/#/assets/vendor/hotwired/#g' \
  -e 's#/assets/@symfony/#/assets/symfony/#g' \
  public/assets/importmap.json
```

## 4. Cache et permissions

```bash
php bin/console cache:clear --env=prod
find public -type d -exec chmod 755 {} +
find public -type f -exec chmod 644 {} +
```

## 5. Vérifications

```bash
test -s public/build/app.css
test -s public/assets/importmap.json
! grep -q '/assets/vendor/@hotwired/' public/assets/importmap.json
! grep -q '/assets/@symfony/' public/assets/importmap.json
curl -I https://olympia-madagascar.mg/build/app.css
```

La réponse HTTP attendue pour les assets est `200`. Effectuer ensuite un rechargement forcé avec `Ctrl + F5`.
