# Mini LinkedIn — API REST

API backend d'une plateforme de recrutement développée avec **Laravel 13** et **JWT**.  
La plateforme met en relation des **candidats**, des **recruteurs** et un **administrateur**.

---

## Prérequis

- PHP >= 8.3
- Composer
- SQLite (inclus par défaut) ou MySQL
- Node.js >= 18 (optionnel, pour le frontend Vite)

---

## Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/aymenazzahidi/Mini_Linkedin.git
cd Mini_Linkedin

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. Générer la clé de l'application
php artisan key:generate

# 5. Générer le secret JWT
php artisan jwt:secret

# 6. Exécuter les migrations
php artisan migrate

# 7. Peupler la base de données (optionnel)
php artisan db:seed

# 8. Lancer le serveur
php artisan serve
```

L'API est disponible sur `http://localhost:8000/api`.

---

## Récapitulatif des routes

### Authentification — public

| Méthode | Endpoint | Description |
|---|---|---|
| `POST` | `/api/register` | Inscription (`role` : `candidat` ou `recruteur`) |
| `POST` | `/api/login` | Connexion — retourne un token JWT |
| `POST` | `/api/logout` | Déconnexion *(auth requise)* |
| `POST` | `/api/refresh` | Rafraîchir le token *(auth requise)* |
| `GET` | `/api/me` | Utilisateur connecté *(auth requise)* |

---

### Profil candidat — `role: candidat`

| Méthode | Endpoint | Description |
|---|---|---|
| `POST` | `/api/profil` | Créer son profil (une seule fois) |
| `GET` | `/api/profil` | Consulter son profil |
| `PUT` | `/api/profil` | Modifier son profil |
| `POST` | `/api/profil/competences` | Ajouter une compétence (avec niveau) |
| `DELETE` | `/api/profil/competences/{competence}` | Retirer une compétence |

---

### Offres d'emploi

| Méthode | Endpoint | Rôle | Description |
|---|---|---|---|
| `GET` | `/api/offres` | Public | Liste des offres actives *(filtre : localisation, type — 10/page)* |
| `GET` | `/api/offres/{offre}` | Public | Détail d'une offre |
| `POST` | `/api/offres` | Recruteur | Créer une offre |
| `PUT` | `/api/offres/{offre}` | Recruteur | Modifier son offre |
| `DELETE` | `/api/offres/{offre}` | Recruteur | Supprimer son offre |

---

### Candidatures

| Méthode | Endpoint | Rôle | Description |
|---|---|---|---|
| `POST` | `/api/offres/{offre}/candidater` | Candidat | Postuler à une offre |
| `GET` | `/api/mes-candidatures` | Candidat | Voir ses propres candidatures |
| `GET` | `/api/offres/{offre}/candidatures` | Recruteur | Voir les candidatures reçues |
| `PATCH` | `/api/candidatures/{candidature}/statut` | Recruteur | Changer le statut (`en_attente` / `acceptee` / `refusee`) |

---

### Administration — `role: admin`

| Méthode | Endpoint | Description |
|---|---|---|
| `GET` | `/api/admin/users` | Lister tous les utilisateurs |
| `DELETE` | `/api/admin/users/{user}` | Supprimer un utilisateur |
| `PATCH` | `/api/admin/offres/{offre}` | Activer / désactiver une offre |

---

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
