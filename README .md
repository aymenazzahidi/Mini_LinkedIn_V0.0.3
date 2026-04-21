# Mini LinkedIn — API Backend

API REST d'une plateforme de recrutement construite avec Laravel 13. Elle met en relation des **candidats** qui postulent à des offres d'emploi, et des **recruteurs** qui publient et gèrent ces offres. Un **administrateur** supervise l'ensemble de la plateforme.

---

## Stack technique

- **PHP** 8.3
- **Laravel** 13
- **JWT** via `php-open-source-saver/jwt-auth`
- **MySQL**

---

## Prérequis

- PHP >= 8.3 avec les extensions `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`
- Composer
- MySQL >= 8.0

---

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/votre-utilisateur/mini-linkedin.git
cd mini-linkedin
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Ouvrir `.env` et renseigner la connexion MySQL :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_linkedin
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

Créer la base de données si elle n'existe pas encore :

```sql
CREATE DATABASE mini_linkedin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Générer la clé JWT

```bash
php artisan jwt:secret
```

Cette commande ajoute automatiquement `JWT_SECRET` dans votre `.env`.

### 5. Lancer les migrations et le seeder

```bash
php artisan migrate --seed
```

Le seeder crée automatiquement :
- 2 comptes administrateur
- 5 recruteurs, chacun avec 2 à 3 offres
- 10 candidats avec profil et compétences associées

Tous les comptes générés ont le mot de passe : `password`

### 6. Démarrer le serveur

```bash
php artisan serve
```

L'API est accessible sur `http://localhost:8000/api`.

---

## Authentification

L'API utilise JWT (Bearer Token). Après connexion, inclure le token dans chaque requête protégée :

```
Authorization: Bearer <votre_token>
```

---

## Collection Postman

Le dossier `postman/` contient la collection à importer directement dans Postman. Elle couvre l'ensemble des endpoints et inclut des scénarios d'erreur pour chaque cas (401, 403, 422).

### Gestion automatique des tokens

La collection utilise des **variables de collection** pour gérer les tokens JWT des trois rôles sans aucune copie-colle manuelle entre les requêtes.

**Pourquoi ce mécanisme ?**

Lors des tests, on jongle constamment entre trois sessions simultanées : candidat, recruteur et admin. Sans automatisation, il faudrait après chaque connexion copier le token retourné par l'API et le coller à la main dans chaque requête suivante. C'est lent, source d'erreurs, et rend l'ordre d'exécution des requêtes contraignant.

**Comment ça fonctionne ?**

Trois variables sont déclarées au niveau de la collection :

| Variable | Rôle |
|----------|------|
| `token_candidat` | Token JWT du candidat connecté |
| `token_recruteur` | Token JWT du recruteur connecté |
| `token_admin` | Token JWT de l'administrateur connecté |

Chaque requête de connexion (ou d'inscription) possède un **script de test** qui s'exécute automatiquement dès que la réponse arrive. Si la requête réussit, il extrait le `access_token` de la réponse et met à jour la variable correspondante :

```javascript
// Exemple sur "Connexion (candidat)"
if (pm.response.code === 200) {
    pm.collectionVariables.set('token_candidat', pm.response.json().access_token);
}
```

Ensuite, toutes les requêtes qui nécessitent ce rôle référencent simplement la variable dans leur header `Authorization` :

```
Authorization: Bearer {{token_candidat}}
```

Postman remplace `{{token_candidat}}` par la valeur stockée au moment de l'envoi. Il suffit donc de lancer une fois la requête de connexion pour que toutes les requêtes du même rôle fonctionnent immédiatement, sans aucune intervention manuelle.

**Utilisation**

1. Importer le fichier `postman/Mini_LinkedIn_API.json` dans Postman
2. Vérifier que la variable `base_url` pointe vers `http://localhost:8000/api`
3. Exécuter d'abord les requêtes de connexion dans le dossier **Auth** pour alimenter les tokens
4. Toutes les autres requêtes sont prêtes à l'emploi

---

## Récapitulatif des routes

### Auth — public

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/register` | Créer un compte (`candidat` ou `recruteur`) |
| POST | `/api/login` | Se connecter, retourne un token JWT |
| POST | `/api/logout` | Se déconnecter |
| POST | `/api/refresh` | Rafraîchir le token |
| GET  | `/api/me` | Infos de l'utilisateur connecté |

### Offres — lecture publique

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/offres` | Liste des offres actives (pagination, filtres `localisation` et `type`) |
| GET | `/api/offres/{id}` | Détail d'une offre |

### Profil — candidat uniquement

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST   | `/api/profil` | Créer son profil (une seule fois) |
| GET    | `/api/profil` | Consulter son profil |
| PUT    | `/api/profil` | Modifier son profil |
| POST   | `/api/profil/competences` | Ajouter une compétence avec niveau |
| DELETE | `/api/profil/competences/{id}` | Retirer une compétence |

### Candidatures — candidat uniquement

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/offres/{id}/candidater` | Postuler à une offre |
| GET  | `/api/mes-candidatures` | Lister ses candidatures |

### Offres & candidatures — recruteur uniquement

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST   | `/api/offres` | Créer une offre |
| PUT    | `/api/offres/{id}` | Modifier son offre |
| DELETE | `/api/offres/{id}` | Supprimer son offre |
| GET    | `/api/offres/{id}/candidatures` | Voir les candidatures reçues |
| PATCH  | `/api/candidatures/{id}/statut` | Changer le statut (`en_attente`, `acceptee`, `refusee`) |

### Administration — admin uniquement

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET    | `/api/admin/users` | Lister tous les utilisateurs |
| DELETE | `/api/admin/users/{id}` | Supprimer un compte |
| PATCH  | `/api/admin/offres/{id}` | Activer ou désactiver une offre |

---

## Règles métier

- Un candidat ne peut créer qu'un seul profil.
- Un candidat ne peut postuler qu'une seule fois à la même offre.
- Un recruteur ne peut modifier ou supprimer que ses propres offres.
- Toute tentative d'accès à la ressource d'un autre utilisateur retourne une erreur `403`.
- La liste des offres supporte la pagination (10 par page), le tri par date de création (desc), et les filtres par `localisation` et `type`.

---

## Events & Listeners

Deux événements sont déclenchés automatiquement et loggés dans `storage/logs/candidatures.log` :

- **`CandidatureDeposee`** — enregistre la date, le nom du candidat et le titre de l'offre à chaque nouvelle candidature.
- **`StatutCandidatureMis`** — enregistre l'ancien statut, le nouveau et la date à chaque changement de statut.

## Structure du projet

```
app/
├── Http/Controllers/    # AuthController, ProfilController, OffreController,
│                        # CandidatureController, AdminController
├── Http/Middleware/     # RoleMiddleware (candidat | recruteur | admin)
├── Models/              # User, Profil, Competence, Offre, Candidature
├── Events/              # CandidatureDeposee, StatutCandidatureMis
├── Listeners/           # LogCandidatureDeposee, LogStatutCandidatureMis
└── Providers/           # EventServiceProvider

database/
├── migrations/          # Toutes les migrations
├── factories/           # UserFactory, ProfilFactory, OffreFactory, CompetenceFactory
└── seeders/             # DatabaseSeeder (2 admins, 5 recruteurs, 10 candidats)

routes/
└── api.php              # Toutes les routes de l'API
```
