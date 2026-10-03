# Procédure de déploiement Olympia

Le workflow GitHub Actions transfère le code par FTP, installe les dépendances et compile automatiquement les assets CSS et JavaScript sur le serveur via SSH.

## Déclencher le déploiement

```bash
git add .
git commit -m "Mise à jour du site"
git push origin main
```

Le workflow `Deploy Olympia` se lance automatiquement sur chaque push vers `main`.

## Opérations exécutées automatiquement

Le workflow :

1. transfère les sources par FTPS ;
2. exclut les dossiers générés (`public/assets`, `public/build`, `vendor`, `node_modules`) du transfert ;
3. installe les dépendances PHP avec Composer ;
4. installe les dépendances JavaScript avec `npm ci` ;
5. compile Tailwind avec `npm run build` ;
6. installe l’importmap avec `php bin/console importmap:install` ;
7. compile les assets Symfony avec `php bin/console asset-map:compile` ;
8. renomme les chemins `@hotwired` et `@symfony` si l’hébergeur les bloque ;
9. exécute les migrations et vide le cache Symfony ;
10. applique les permissions `755` aux dossiers et `644` aux fichiers publics.

Aucune commande manuelle n’est nécessaire après un déploiement réussi.

## Connexion SSH pour vérifier le serveur

```bash
ssh olympiam@web1.simafri.cloud
cd /home/olympiam/domains/olympia-madagascar.mg/public_html
```

Vérifier les fichiers générés :

```bash
test -s public/build/app.css
test -s public/assets/importmap.json
find public/assets/vendor/hotwired -type f -name '*.js'
```

Vérifier que l’importmap n’utilise plus les anciens chemins :

```bash
! grep -q '/assets/vendor/@hotwired/' public/assets/importmap.json
! grep -q '/assets/@symfony/' public/assets/importmap.json
```

Tester les assets depuis Internet :

```bash
curl -I https://olympia-madagascar.mg/build/app.css
```

La réponse attendue est `200`. Dans le navigateur, utiliser `Ctrl + F5` après le déploiement.

## Secrets GitHub nécessaires

Dans `Settings > Secrets and variables > Actions`, configurer :

- `FTP_HOST`
- `FTP_USER`
- `FTP_PASSWORD`
- `SSH_HOST`
- `SSH_USER`
- `SSH_PRIVATE_KEY`
- `SSH_SERVER_DIR` (facultatif)
- `APP_SECRET`
- `HOST_BASE`
- `NOM_BASE`
- `NOM_UTILISATEUR`
- `MOT_DE_PASSE`

Ne jamais enregistrer ces valeurs dans Git.
