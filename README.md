# EcoTrack

Application web de **suivi de consommation** (énergie), développée avec Symfony 7.

## Prérequis

- PHP 8.2+
- Composer
- Docker & Docker Compose (pour MySQL)

## Installation

1. **Cloner le projet et installer les dépendances**

   ```bash
   composer install
   ```

2. **Démarrer la base de données (MySQL + phpMyAdmin)**

   ```bash
   docker-compose up -d
   ```

   - MySQL : `localhost:3307`  
   - phpMyAdmin : http://localhost:8081

3. **Configurer l’environnement**

   Copier `.env` en `.env.local` si besoin et vérifier `DATABASE_URL` (par défaut : `127.0.0.1:3307`, base `ecotrack`).

4. **Créer la base et les tables**

   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

5. **Lancer l’application**

   ```bash
   symfony server:start
   ```

   Ou avec le serveur PHP intégré :

   ```bash
   php -S localhost:8000 -t public
   ```

   Accès : http://localhost:8000 (ou l’URL indiquée par `symfony server:start`).

## Fonctionnalités

- **Inscription** : `/register`
- **Connexion / Déconnexion** : `/login`, `/logout`
- **Tableau de bord** : `/dashboard` — suivi des consommations par type d’énergie

## Stack technique

- **Backend** : Symfony 7, PHP 8.2+
- **Base de données** : MySQL 8 (Doctrine ORM, migrations)
- **Front** : Twig, Stimulus
- **Sécurité** : Symfony Security (authentification)

## Commandes utiles

| Commande | Description |
|----------|-------------|
| `composer install` | Installer les dépendances |
| `docker-compose up -d` | Démarrer MySQL et phpMyAdmin |
| `php bin/console doctrine:migrations:migrate` | Exécuter les migrations |
| `php bin/console doctrine:fixtures:load` | Charger les fixtures (données de test) |

---

*EcoTrack — Suivi de consommation énergétique*
