# Source de vérité des enseignes

La base de production est la seule source de vérité pour les enseignes, les boutiques et les restaurants présents à Olympia.

Il ne faut pas maintenir de liste statique d’enseignes dans le code ou dans ce document. Toute mise à jour doit d’abord être vérifiée à partir des associations présentes en base :

- `enseigne` contient l’identité de l’enseigne ;
- `boutique` contient les boutiques publiables et leur description ;
- `restaurant` contient les restaurants publiables et leur description.

Les enseignes sans association `boutique` ou `restaurant` sont considérées comme des fiches incomplètes et ne doivent pas être recréées automatiquement à partir d’une ancienne liste.
