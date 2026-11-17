# Rapport d’audit avant livraison — Olympia Madagascar

Date de contrôle : 17 novembre 2026  
Site contrôlé : [olympia-madagascar.mg](https://olympia-madagascar.mg/)

## Conclusion

Le site est accessible et les principales pages publiques répondent correctement. La validation finale doit toutefois rester **conditionnelle** tant que les deux points critiques suivants ne sont pas corrigés sur le serveur :

1. Le mode debug est exposé en production.
2. Le fichier `.env` suivi par Git contient une URL de base de données de production. Le mot de passe n’est pas reproduit dans ce rapport, mais il doit être considéré comme compromis et être changé.

Les autres contrôles indiquent une base fonctionnelle, mais le déploiement des assets et le suivi des migrations doivent être fiabilisés avant la livraison.

## Contrôles fonctionnels production

| Fonctionnalité | Résultat | Détail |
|---|---:|---|
| Accueil | OK | HTTP 200 |
| Enseignes | OK | HTTP 200 |
| Restaurants / food court | OK | HTTP 200 |
| Actualités | OK | HTTP 200 |
| Événements | OK | HTTP 200 |
| Promotions | OK | HTTP 200 |
| Services | OK | HTTP 200 |
| Informations pratiques | OK | HTTP 200 |
| Contact | OK | HTTP 200 |
| Mentions légales | OK | HTTP 200 |
| Confidentialité | OK | HTTP 200 |
| CGU | OK | HTTP 200 |
| Robots / sitemap | OK | HTTP 200 |
| Connexion galerie | OK | Redirection vers la connexion attendue |
| Connexion back-office | OK | Page de connexion accessible ; accès protégé |
| Assets de l’accueil | OK | 26 ressources contrôlées, aucune ressource cassée détectée |
| Liens de détail | OK | 20 liens contrôlés, aucune erreur détectée |

Les trois routes d’intégration Make sont bien publiées en POST. Un appel GET renvoie correctement 405 avec `Allow: POST`. Aucun POST valide n’a été exécuté sur la production afin de ne pas créer de contenu pendant l’audit.

## Données contrôlées en base

- 34 boutiques présentes ; descriptions, catégories et horaires contrôlés présents.
- 12 restaurants présents ; descriptions, catégories et horaires contrôlés présents.
- Les horaires contrôlés sont réglés sur « À partir de 9h ».
- Des descriptions d’enseigne ont été supprimées conformément à la règle métier : la description est portée par la boutique ou le restaurant.
- 20 anciennes enseignes ne sont reliées ni à une boutique ni à un restaurant. Elles doivent être vérifiées dans le back-office avant archivage ou suppression.

## Contrôles techniques locaux

- Syntaxe PHP : OK sur le dossier `src`.
- Syntaxe Twig : OK sur les 52 templates.
- `composer validate` : OK.
- Mapping Doctrine : OK.
- Tests automatisés : insuffisants ; `tests` ne contient actuellement qu’un bootstrap, sans scénario fonctionnel réel.
- Le smoke-test public a validé les principales routes, mais il ne couvre pas encore les pages légales, la galerie, les détails et les formulaires.

## Points bloquants avant livraison

### Critique — debug exposé en production

Les réponses 404 et 405 de production contiennent `X-Debug-Exception`, `X-Debug-Exception-File` et le chemin interne du serveur. Cela indique que Symfony fonctionne avec le debug activé malgré le déploiement prévu avec `APP_DEBUG=0`.

À faire immédiatement sur le serveur :

- vérifier `php bin/console about --env=prod` et confirmer `Debug false` ;
- vérifier les variables d’environnement réellement chargées par PHP/Apache-FPM ;
- supprimer le cache Symfony après correction ;
- retester une URL inexistante et les trois routes Make.

### Critique — secret de base de données dans Git

`.env` est suivi par Git et contient une connexion de production. Il faut :

- changer le mot de passe de la base ;
- retirer `.env` du suivi Git et vérifier l’historique ;
- conserver uniquement les secrets dans les variables serveur ou les secrets GitHub ;
- vérifier que `.env.local` reste ignoré et protégé par les permissions du serveur.

### Élevé — clés API Make de secours

Le workflow utilise `test-key` comme valeur de secours si les trois secrets Make ne sont pas définis. En production, le workflow doit échouer immédiatement si une clé manque ; aucune valeur par défaut ne doit être acceptée.

### Élevé — compilation des assets au déploiement

Le workflow exclut `public/build` et n’exécute pas `npm run build` sur le serveur. Il affiche également que la compilation JavaScript reste manuelle. Toute modification Tailwind, comme la correction des cartes d’actualités, nécessite donc une compilation explicite sur le serveur ou une modification du pipeline.

### Élevé — migrations

La commande locale de statut des migrations sur la base configurée échoue avec « metadata storage is not up to date ». Le workflow production exécute les migrations mais ne lance pas `doctrine:migrations:sync-metadata-storage`, contrairement au workflow de préproduction. Ce point doit être vérifié puis régularisé avant une nouvelle migration.

## Retours code et sécurité

- Ajouter des tests PHPUnit/functional pour les pages publiques, les formulaires contact/newsletter, les imports Make et les droits back-office.
- Ajouter au smoke-test les routes `/actualites`, `/confidentialite`, `/cgu`, `/mentions-legales`, `/galerie`, les pages de détail et un contrôle de ressources.
- Ajouter les en-têtes HTTP de sécurité : HSTS, `X-Content-Type-Options`, `Content-Security-Policy`, `Referrer-Policy` et `Permissions-Policy`.
- Le cookie de session est déjà `Secure`, `HttpOnly` et `SameSite=Lax`, mais la version Apache est divulguée dans les réponses ; réduire cette information côté serveur.
- Protéger les commandes de seed/demo contre une exécution accidentelle en production et vérifier que les images de démonstration encore présentes dans `public/images/demo` ne sont pas utilisées par le site public.
- Vérifier les 20 enseignes orphelines avant de les archiver ; ne pas les supprimer sans confirmation métier.

## Correction réalisée pendant cet audit

Sur la page des actualités, les cartes n’affichent plus le texte par-dessus l’image. L’image est maintenant isolée dans sa propre zone, tandis que le badge, la date, le titre, l’extrait et le bouton « En savoir plus » sont affichés dans un bloc blanc séparé.

Fichier modifié : `templates/actualite/index.html.twig`  
Validation : `php bin/console lint:twig templates --env=prod` et `npm run build` réussis.

Le contrat Make des actualités a également été renforcé : le champ `title` généré par l’IA, ou son alias `titre`, est maintenant obligatoire pour l’API. Il est utilisé pour le titre et le slug de l’actualité, puis renvoyé dans la réponse JSON.

## Ordre recommandé avant livraison

1. Désactiver le debug production et changer le mot de passe de base.
2. Supprimer les valeurs `test-key` du workflow et vérifier les trois secrets Make.
3. Fiabiliser la compilation et le transfert des assets.
4. Régulariser le metadata storage des migrations avec une sauvegarde préalable.
5. Vérifier les enseignes orphelines et les contenus réellement affichés.
6. Effectuer un parcours manuel final sur mobile et desktop, puis retester les formulaires et les imports Make en préproduction.
