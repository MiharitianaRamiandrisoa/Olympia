# Intégrations Make

Les trois imports utilisent `POST` et attendent un objet JSON.

## Actualités

URL : `https://olympia-madagascar.mg/api/integrations/make/actualites`

Header : `X-Actualite-Api-Key`

Clé serveur : `ACTUALITE_IMPORT_API_KEY`

Le scénario Make doit analyser la publication puis envoyer le titre clair et précis généré par l’IA dans `title`. Le champ `titre` est également accepté comme alias. Le titre envoyé est utilisé pour le titre de l’actualité, son slug et son affichage sur le site ; il est obligatoire.

Exemple :

```json
{
  "external_id": "facebook-post-123",
  "title": "Jaintilal K ouvre sa nouvelle boutique de bijoux à Olympia",
  "content": "Texte complet de la publication Facebook...",
  "excerpt": "Découvrez la nouvelle boutique Jaintilal K au centre commercial Olympia.",
  "published_at": "2026-11-17T09:00:00+03:00",
  "permalink_url": "https://www.facebook.com/...",
  "image_url": "https://scontent.xx.fbcdn.net/...",
  "active": true,
  "featured": false
}
```

`external_id` et `title` sont obligatoires. Si le titre est absent ou vide, l’API répond `422` et aucune actualité n’est créée. La réponse contient également le titre enregistré dans le champ `title`.

## Événements

URL : `https://olympia-madagascar.mg/api/integrations/make/evenements`

Header : `X-Evenement-Api-Key`

Clé serveur : `EVENEMENT_IMPORT_API_KEY`

Exemple :

```json
{
  "external_id": "facebook-event-123",
  "title": "Animation enfants",
  "description": "Une animation gratuite pour les enfants.",
  "start_at": "2026-11-20T14:00:00+03:00",
  "end_at": "2026-11-20T17:00:00+03:00",
  "location": "Galerie Olympia",
  "category": "Animations",
  "enseigne": "Olympia",
  "image_url": "https://scontent.xx.fbcdn.net/...",
  "active": true,
  "featured": false
}
```

`external_id`, `title` et `start_at` sont obligatoires. Pour un événement, `enseigne` peut être le nom d’une enseigne existante ou un texte libre. Une valeur inconnue est enregistrée comme `organisateur`.

## Promotions

URL : `/api/integrations/make/promotions`

Header : `X-Promotion-Api-Key`

Clé serveur : `PROMOTION_IMPORT_API_KEY`

Exemple :

```json
{
  "external_id": "facebook-offer-456",
  "title": "-20% sur la collection été",
  "description": "Profitez de l’offre en magasin.",
  "conditions": "Valable jusqu’au 30 novembre 2026.",
  "start_at": "2026-11-14T00:00:00+03:00",
  "end_at": "2026-11-30T23:59:59+03:00",
  "category": "Mode",
  "enseigne": "AFG BANK",
  "image_url": "https://scontent.xx.fbcdn.net/...",
  "active": true,
  "featured": false
}
```

`external_id`, `title`, `start_at` et `enseigne` sont obligatoires. L’enseigne peut être envoyée par identifiant, slug ou nom. Les catégories sont recherchées par identifiant ou slug ; une nouvelle catégorie est créée si nécessaire.

Un même `external_id` ne sera importé qu’une seule fois pour chaque type de contenu. Une seconde requête retourne `action: skipped`.

Les images distantes utilisent le même contrôle que l’import des actualités : elles doivent provenir d’un domaine Facebook autorisé.
