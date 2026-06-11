
-- Base de données : app_commande_repas (Gabon)
-- Projet 12 – Application de Commande de Repas en Ligne
-- Moteur : MySQL 8+  |  Charset : utf8mb4  |  Monnaie : XAF

CREATE DATABASE IF NOT EXISTS app_commande_repas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE app_commande_repas;


-- 1. UTILISATEUR  (Client + Administrateur)

CREATE TABLE utilisateur (
  id_utilisateur    INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  nom               VARCHAR(80)       NOT NULL,
  prenom            VARCHAR(80)       NOT NULL,
  email             VARCHAR(150)      NOT NULL UNIQUE,
  mot_de_passe      VARCHAR(255)      NOT NULL,           -- bcrypt
  telephone         VARCHAR(20)       DEFAULT NULL,
  adresse_livraison TEXT              DEFAULT NULL,
  role              ENUM('client','admin') NOT NULL DEFAULT 'client',
  created_at        TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_utilisateur)
) ENGINE=InnoDB;


-- 2. RESTAURANT

CREATE TABLE restaurant (
  id_restaurant     INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  nom               VARCHAR(120)      NOT NULL,
  adresse           TEXT              NOT NULL,
  ville             VARCHAR(80)       NOT NULL DEFAULT 'Libreville',
  telephone         VARCHAR(20)       DEFAULT NULL,
  description       TEXT              DEFAULT NULL,
  logo              VARCHAR(255)      DEFAULT NULL,
  statut            ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
  created_at        TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_restaurant)
) ENGINE=InnoDB;


-- 3. CATEGORIE_MENU  (appartient à un restaurant)

CREATE TABLE categorie_menu (
  id_categorie      INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  id_restaurant     INT UNSIGNED      NOT NULL,
  libelle           VARCHAR(100)      NOT NULL,
  description       TEXT              DEFAULT NULL,
  PRIMARY KEY (id_categorie),
  FOREIGN KEY (id_restaurant) REFERENCES restaurant(id_restaurant)
    ON DELETE CASCADE
) ENGINE=InnoDB;


-- 4. PLAT  (appartient à une catégorie)

CREATE TABLE plat (
  id_plat           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  id_categorie      INT UNSIGNED      NOT NULL,
  nom               VARCHAR(120)      NOT NULL,
  description       TEXT              DEFAULT NULL,
  prix              DECIMAL(10,2)     NOT NULL,           -- XAF
  photo             VARCHAR(255)      DEFAULT NULL,
  disponible        TINYINT(1)        NOT NULL DEFAULT 1,
  PRIMARY KEY (id_plat),
  FOREIGN KEY (id_categorie) REFERENCES categorie_menu(id_categorie)
    ON DELETE CASCADE
) ENGINE=InnoDB;


-- 5. COMMANDE

CREATE TABLE commande (
  id_commande       INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  id_utilisateur    INT UNSIGNED      NOT NULL,
  id_restaurant     INT UNSIGNED      NOT NULL,
  date_heure        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  montant_total     DECIMAL(10,2)     NOT NULL,           -- XAF
  statut            ENUM('en_attente','confirmée','en_préparation',
                         'en_livraison','livrée','annulée')
                                      NOT NULL DEFAULT 'en_attente',
  type_livraison    ENUM('livraison','retrait') NOT NULL,
  adresse_livraison TEXT              DEFAULT NULL,
  notes             TEXT              DEFAULT NULL,
  PRIMARY KEY (id_commande),
  FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur),
  FOREIGN KEY (id_restaurant)  REFERENCES restaurant(id_restaurant)
) ENGINE=InnoDB;


-- 6. LIGNE_COMMANDE  (détail d'une commande)

CREATE TABLE ligne_commande (
  id_ligne          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  id_commande       INT UNSIGNED      NOT NULL,
  id_plat           INT UNSIGNED      NOT NULL,
  quantite          TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  prix_unitaire     DECIMAL(10,2)     NOT NULL,           -- XAF (snapshot)
  sous_total        DECIMAL(10,2)     NOT NULL,           -- XAF
  notes_specifiques TEXT              DEFAULT NULL,
  PRIMARY KEY (id_ligne),
  FOREIGN KEY (id_commande) REFERENCES commande(id_commande)
    ON DELETE CASCADE,
  FOREIGN KEY (id_plat)     REFERENCES plat(id_plat)
) ENGINE=InnoDB;


-- 7. PAIEMENT  (1 paiement par commande)

CREATE TABLE paiement (
  id_paiement           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  id_commande           INT UNSIGNED  NOT NULL UNIQUE,
  mode                  ENUM('mobile_money','espèces','carte') NOT NULL,
  montant               DECIMAL(10,2) NOT NULL,           -- XAF
  statut                ENUM('en_attente','validé','refusé')
                                      NOT NULL DEFAULT 'en_attente',
  date_heure            DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reference_transaction VARCHAR(100)  DEFAULT NULL UNIQUE,
  PRIMARY KEY (id_paiement),
  FOREIGN KEY (id_commande) REFERENCES commande(id_commande)
) ENGINE=InnoDB;


-- 8. AVIS  (1 avis par commande, par utilisateur)

CREATE TABLE avis (
  id_avis         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  id_utilisateur  INT UNSIGNED    NOT NULL,
  id_restaurant   INT UNSIGNED    NOT NULL,
  id_commande     INT UNSIGNED    NOT NULL,
  note            TINYINT UNSIGNED NOT NULL,
  commentaire     TEXT            DEFAULT NULL,
  date_avis       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_note CHECK (note BETWEEN 1 AND 5),
  PRIMARY KEY (id_avis),
  FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur),
  FOREIGN KEY (id_restaurant)  REFERENCES restaurant(id_restaurant),
  FOREIGN KEY (id_commande)    REFERENCES commande(id_commande)
) ENGINE=InnoDB;


-- 9. NOTIFICATION

CREATE TABLE notification (
  id_notif        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  id_utilisateur  INT UNSIGNED    NOT NULL,
  id_commande     INT UNSIGNED    DEFAULT NULL,
  message         TEXT            NOT NULL,
  type            ENUM('commande','paiement','statut','promo') NOT NULL,
  date_envoi      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lu              TINYINT(1)      NOT NULL DEFAULT 0,
  PRIMARY KEY (id_notif),
  FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
    ON DELETE CASCADE,
  FOREIGN KEY (id_commande)    REFERENCES commande(id_commande)
    ON DELETE SET NULL
) ENGINE=InnoDB;