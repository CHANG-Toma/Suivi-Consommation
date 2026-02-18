# EcoTrack

Application web de **suivi de consommation énergétique**, développée avec Symfony 7.

---

## Prérequis

Installez les outils suivants **avant** de commencer :

| Outil | Version minimale | Vérification |
|-------|------------------|--------------|
| **PHP** | 8.2+ | `php -v` |
| **Composer** | 2.x | `composer -V` |
| **Node.js** | 18+ (LTS recommandé) | `node -v` |
| **npm** | 9+ | `npm -v` |
| **Docker** + **Docker Compose** | — | `docker -v` et `docker compose version` |

> **Symfony CLI** est optionnel ; vous pouvez utiliser le serveur PHP intégré à la place.

---

## Installation (ordre recommandé)

Suivez les étapes **dans l’ordre** pour éviter les erreurs.

### 1. Cloner le projet

```bash
git clone <url-du-repo> EcoTrack
cd EcoTrack
```

### 2. Installer les dépendances PHP

```bash
composer install
```

- Ne pas ignorer les scripts post-install si Composer les propose.
- En cas d’erreur de version PHP : vérifier `php -v` (≥ 8.2).

### 3. Installer les dépendances Node.js

```bash
npm install
```

- Nécessaire pour Tailwind CSS (styles, sidebar, menu mobile).

### 4. Compiler le CSS (important pour éviter les bugs d’affichage)

```bash
npm run build
```

- **À faire avant le premier lancement** : sans cette étape, la sidebar, le menu du bas et le responsive ne s’affichent pas correctement.
- En développement, vous pouvez lancer en parallèle : `npm run watch` (recompile le CSS à chaque modification).

### 5. Démarrer la base de données (Docker)

```bash
docker compose up -d
```

- Démarre **MySQL** et **phpMyAdmin**.
- Attendre ~30 secondes que MySQL soit prêt avant la prochaine étape.

**Accès :**

- **MySQL** : `127.0.0.1:3307` (utilisateur : `ecotrack_user`, mot de passe : `ecotrack_password`, base : `ecotrack`)
- **phpMyAdmin** : http://localhost:8082

### 6. Configurer l’environnement

Le fichier `.env` est déjà configuré pour Docker (port **3307**, utilisateur `ecotrack_user`).

- **Avec Docker (recommandé)** : ne rien changer, `DATABASE_URL` dans `.env` convient.
- **Sans Docker** : créer `.env.local` à partir de `.env.local.example` et adapter `DATABASE_URL` à votre MySQL local.

Exemple `.env.local` (sans Docker) :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/ecotrack?serverVersion=8.0.32&charset=utf8mb4"
```

### 7. Créer la base et les tables

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

- À exécuter **une fois** MySQL démarré.
- En cas d’erreur de connexion : vérifier que les conteneurs tournent (`docker compose ps`) et que le port 3307 est bien utilisé par MySQL.

### 8. Charger les données de base (types d’énergie)

```bash
php bin/console doctrine:fixtures:load --append
```

- Charge les types d’énergie (Électricité, Gaz, Eau, etc.).
- `--append` évite de vider la base ; retirer cette option pour repartir de zéro.

### 9. Lancer l’application

**Option A – Symfony CLI (si installé) :**

```bash
symfony server:start
```

**Option B – Serveur PHP intégré :**

```bash
php -S localhost:8000 -t public
```

- Ouvrir : **http://localhost:8000** (ou l’URL affichée par `symfony server:start`).

---

## Récapitulatif des commandes (copier-coller)

Dans l’ordre, une fois le projet cloné :

```bash
composer install
npm install
npm run build
docker compose up -d
# Attendre ~30 s que MySQL démarre
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --append
php -S localhost:8000 -t public
```

Puis aller sur http://localhost:8000 et s’inscrire / se connecter.

---

## Fonctionnalités

- **Page d’accueil** : `/`
- **Inscription** : `/register`
- **Connexion / Déconnexion** : `/login`, `/logout`
- **Tableau de bord** : `/dashboard` — consommations par type d’énergie, graphiques, export PDF
- **Logements** : gestion des logements
- **Relevés** : saisie et historique des relevés
- **Alertes** : seuils et notifications
- **Profil** : informations personnelles et mot de passe

---

## Stack technique

- **Backend** : Symfony 7, PHP 8.2+
- **Base de données** : MySQL 8 (Doctrine ORM, migrations)
- **Front** : Twig, Tailwind CSS (pas de JavaScript framework)
- **Sécurité** : Symfony Security (authentification)

---

## Commandes utiles

| Commande | Description |
|----------|-------------|
| `composer install` | Installer les dépendances PHP |
| `npm install` | Installer les dépendances Node.js |
| `npm run build` | Compiler le CSS une fois (production / premier run) |
| `npm run watch` | Compiler le CSS en continu (développement) |
| `docker compose up -d` | Démarrer MySQL et phpMyAdmin |
| `docker compose down` | Arrêter les conteneurs |
| `php bin/console doctrine:migrations:migrate` | Exécuter les migrations |
| `php bin/console doctrine:fixtures:load --append` | Charger les fixtures (sans vider la base) |
| `php bin/console cache:clear` | Vider le cache Symfony |

---

*EcoTrack — Suivi de consommation énergétique*
