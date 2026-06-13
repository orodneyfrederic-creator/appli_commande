-- ============================================================
-- Base de données : db_commande
-- Application de Commande de Repas en Ligne — GRS Délices
-- Moteur : MySQL 8+  |  Charset : utf8mb4  |  Monnaie : XAF
-- Tables renommées au PLURIEL pour correspondre aux modèles PHP
-- Conforme à la syntaxe SQL standard (compatibilité linters)
-- ============================================================

DROP DATABASE IF EXISTS `db_commande`;
CREATE DATABASE `db_commande`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_commande`;

-- ============================================================
-- 1. UTILISATEURS
-- ============================================================
CREATE TABLE `utilisateurs` (
  `id_utilisateur`    INT               NOT NULL AUTO_INCREMENT,
  `nom`               VARCHAR(80)       NOT NULL,
  `prenom`            VARCHAR(80)       DEFAULT NULL,
  `email`             VARCHAR(150)      NOT NULL UNIQUE,
  `mot_de_passe`      VARCHAR(255)      NOT NULL,
  `telephone`         VARCHAR(20)       DEFAULT NULL,
  `adresse_livraison` TEXT              DEFAULT NULL,
  `role`              VARCHAR(20)       NOT NULL DEFAULT 'client' CHECK (`role` IN ('client','admin')),
  `created_at`        TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utilisateur`)
);

-- ============================================================
-- 2. RESTAURANTS
-- ============================================================
CREATE TABLE `restaurants` (
  `id_restaurant`     INT               NOT NULL AUTO_INCREMENT,
  `nom`               VARCHAR(120)      NOT NULL,
  `adresse`           TEXT              NOT NULL,
  `ville`             VARCHAR(80)       NOT NULL DEFAULT 'Libreville',
  `telephone`         VARCHAR(20)       DEFAULT NULL,
  `email`             VARCHAR(150)      DEFAULT NULL,
  `description`       TEXT              DEFAULT NULL,
  `logo`              VARCHAR(255)      DEFAULT NULL,
  `actif`             TINYINT           NOT NULL DEFAULT 1,
  `created_at`        TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_restaurant`)
);

-- ============================================================
-- 3. CATEGORIES_MENU (appartient à un restaurant)
-- ============================================================
CREATE TABLE `categories_menu` (
  `id_categorie`      INT               NOT NULL AUTO_INCREMENT,
  `id_restaurant`     INT               NOT NULL,
  `nom`               VARCHAR(100)      NOT NULL,
  `description`       TEXT              DEFAULT NULL,
  PRIMARY KEY (`id_categorie`),
  FOREIGN KEY (`id_restaurant`) REFERENCES `restaurants`(`id_restaurant`)
    ON DELETE CASCADE
);

-- ============================================================
-- 4. PLATS (appartient à une catégorie)
-- ============================================================
CREATE TABLE `plats` (
  `id_plat`           INT               NOT NULL AUTO_INCREMENT,
  `id_categorie`      INT               NOT NULL,
  `nom`               VARCHAR(120)      NOT NULL,
  `description`       TEXT              DEFAULT NULL,
  `prix`              DECIMAL(10,2)     NOT NULL,
  `photo`             VARCHAR(255)      DEFAULT NULL,
  `disponible`        TINYINT           NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_plat`),
  FOREIGN KEY (`id_categorie`) REFERENCES `categories_menu`(`id_categorie`)
    ON DELETE CASCADE
);

-- ============================================================
-- 5. COMMANDES
-- ============================================================
CREATE TABLE `commandes` (
  `id_commande`       INT               NOT NULL AUTO_INCREMENT,
  `id_utilisateur`    INT               NOT NULL,
  `id_restaurant`     INT               NOT NULL,
  `date_commande`     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `montant_total`     DECIMAL(10,2)     NOT NULL,
  `statut`            VARCHAR(30)       NOT NULL DEFAULT 'en_attente' CHECK (`statut` IN ('en_attente','confirmée','en_préparation','en_livraison','livrée','annulée')),
  `type_livraison`    VARCHAR(20)       NOT NULL DEFAULT 'livraison' CHECK (`type_livraison` IN ('livraison','retrait')),
  `adresse_livraison` TEXT              DEFAULT NULL,
  `notes`             TEXT              DEFAULT NULL,
  PRIMARY KEY (`id_commande`),
  FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs`(`id_utilisateur`),
  FOREIGN KEY (`id_restaurant`)  REFERENCES `restaurants`(`id_restaurant`)
);

-- ============================================================
-- 6. LIGNES_COMMANDE (détail d'une commande)
-- ============================================================
CREATE TABLE `lignes_commande` (
  `id_ligne`          INT               NOT NULL AUTO_INCREMENT,
  `id_commande`       INT               NOT NULL,
  `id_plat`           INT               NOT NULL,
  `quantite`          TINYINT           NOT NULL DEFAULT 1,
  `prix_unitaire`     DECIMAL(10,2)     NOT NULL,
  `sous_total`        DECIMAL(10,2)     DEFAULT NULL,
  `notes_specifiques` TEXT              DEFAULT NULL,
  PRIMARY KEY (`id_ligne`),
  FOREIGN KEY (`id_commande`) REFERENCES `commandes`(`id_commande`)
    ON DELETE CASCADE,
  FOREIGN KEY (`id_plat`)     REFERENCES `plats`(`id_plat`)
);

-- ============================================================
-- 7. PAIEMENTS
-- ============================================================
CREATE TABLE `paiements` (
  `id_paiement`           INT           NOT NULL AUTO_INCREMENT,
  `id_commande`           INT           NOT NULL UNIQUE,
  `methode_paiement`      VARCHAR(20)   NOT NULL DEFAULT 'espèces' CHECK (`methode_paiement` IN ('mobile_money','espèces','carte')),
  `montant`               DECIMAL(10,2) NOT NULL,
  `statut`                VARCHAR(20)   NOT NULL DEFAULT 'en_attente' CHECK (`statut` IN ('en_attente','validé','refusé')),
  `date_paiement`         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reference_transaction` VARCHAR(100)  DEFAULT NULL UNIQUE,
  PRIMARY KEY (`id_paiement`),
  FOREIGN KEY (`id_commande`) REFERENCES `commandes`(`id_commande`)
);

-- ============================================================
-- 8. AVIS
-- ============================================================
CREATE TABLE `avis` (
  `id_avis`         INT             NOT NULL AUTO_INCREMENT,
  `id_utilisateur`  INT             NOT NULL,
  `id_restaurant`   INT             NOT NULL,
  `id_commande`     INT             DEFAULT NULL,
  `note`            TINYINT         NOT NULL,
  `commentaire`     TEXT            DEFAULT NULL,
  `date_avis`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `chk_note` CHECK (`note` BETWEEN 1 AND 5),
  PRIMARY KEY (`id_avis`),
  FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs`(`id_utilisateur`),
  FOREIGN KEY (`id_restaurant`)  REFERENCES `restaurants`(`id_restaurant`)
);

-- ============================================================
-- 9. NOTIFICATIONS
-- ============================================================
CREATE TABLE `notifications` (
  `id_notification` INT             NOT NULL AUTO_INCREMENT,
  `id_utilisateur`  INT             NOT NULL,
  `id_commande`     INT             DEFAULT NULL,
  `message`         TEXT            NOT NULL,
  `type`            VARCHAR(20)     NOT NULL CHECK (`type` IN ('commande','paiement','statut','promo')),
  `date_envoi`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lu`              TINYINT         NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_notification`),
  FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs`(`id_utilisateur`)
    ON DELETE CASCADE
);


-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- Admin (password: admin123) + Client test (password: password)
INSERT INTO `utilisateurs` (`nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `role`) VALUES
('Admin', 'Super', 'admin@grsdelices.com',
 '$2y$10$8Kn.hPIxXlREP0KxgFvnDemHJ7bPCJqezV3bRDhH7.bxQ2CxcFjua',
 '+241 065010993', 'admin'),
('Ondo', 'Marie', 'marie.ondo@email.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '+241 077123456', 'client'),
('Nzamba', 'Jean-Baptiste', 'jb.nzamba@email.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '+241 066789012', 'client');

-- Restaurant GRS Délices
INSERT INTO `restaurants` (`nom`, `adresse`, `ville`, `telephone`, `email`, `description`, `actif`) VALUES
('GRS Délices',
 'Boulevard du Bord de Mer, Quartier Louis',
 'Libreville',
 '+241 065010993',
 'grsdelices@gmail.com',
 'Restaurant gastronomique proposant des spécialités africaines et internationales dans un cadre chaleureux au cœur de Libreville. Livraison rapide et fraîcheur garantie.',
 1);

-- Catégories du menu
INSERT INTO `categories_menu` (`id_restaurant`, `nom`, `description`) VALUES
(1, 'Entrées', 'Nos délicieuses entrées pour bien commencer le repas'),
(1, 'Plats Principaux', 'Spécialités africaines et recettes internationales'),
(1, 'Grillades & Viandes', 'Viandes fraîches grillées au feu de bois'),
(1, 'Poissons & Fruits de Mer', 'Pêche du jour et spécialités marines'),
(1, 'Desserts', 'Douceurs artisanales et gourmandises'),
(1, 'Boissons', 'Boissons fraîches, jus naturels et chaudes');

-- Plats
INSERT INTO `plats` (`id_categorie`, `nom`, `description`, `prix`, `disponible`) VALUES
-- Entrées
(1, 'Salade Tropicale',      'Mélange de légumes frais, avocat, mangue verte et vinaigrette citronnée', 3500.00, 1),
(1, 'Brochettes d''Entrée',   'Mini-brochettes de viande marinées aux épices, sauce cacahuète',           4500.00, 1),
(1, 'Soupe de Poisson',      'Bouillon maison parfumé aux herbes locales et légumes du marché',          3000.00, 1),
-- Plats principaux
(2, 'Poulet DG',             'Poulet braisé aux plantains mûrs, tomates, poivrons et sauce épicée',     8500.00, 1),
(2, 'Ndolé aux Crevettes',   'Plat traditionnel camerounais, feuilles de ndolé aux crevettes',          10500.00, 1),
(2, 'Thiéboudienne',         'Riz au poisson sénégalais aux légumes, sauce tomate parfumée',             7500.00, 1),
(2, 'Riz Sauté Spécial',    'Riz sauté à l''œuf, légumes croquants, crevettes et sauce soja',         6500.00, 1),
(2, 'Poulet Nyembwe',        'Plat traditionnel gabonais, poulet à la sauce Nyembwe (graine de palme)',  9000.00, 1),
-- Grillades
(3, 'Grillades Mixtes',      'Assortiment bœuf, poulet, agneau grillés avec frites et salade',         13000.00, 1),
(3, 'Côte de Bœuf',         '350g de côte de bœuf grillée, beurre maître d''hôtel, frites maison',    15000.00, 1),
(3, 'Ailes de Poulet BBQ',  '6 ailes de poulet marinées sauce BBQ maison, coleslaw',                   6500.00, 1),
-- Poissons
(4, 'Tilapia Grillé',        'Tilapia entier grillé aux herbes, citron, sauce pimentée et alloco',      9500.00, 1),
(4, 'Crevettes Sautées',    'Crevettes fraîches sautées à l''ail, beurre et persil, riz blanc',       11000.00, 1),
(4, 'Capitaine Frit',        'Filet de capitaine frit croustillant, frites et sauce tartare maison',    10000.00, 1),
-- Desserts
(5, 'Tarte aux Mangues',     'Tarte maison à la mangue locale, crème pâtissière vanille',               3500.00, 1),
(5, 'Fondant au Chocolat',   'Fondant au chocolat noir 70%, coulis de caramel salé',                   3500.00, 1),
(5, 'Salade de Fruits Frais','Assortiment de fruits tropicaux frais du marché, sirop de menthe',        2500.00, 1),
-- Boissons
(6, 'Jus de Bissap',         'Jus naturel d''hibiscus frais, sucre de canne',                           1500.00, 1),
(6, 'Jus de Gingembre',      'Jus de gingembre frais pressé, citron vert, légèrement épicé',           1500.00, 1),
(6, 'Eau Minérale',          'Bouteille 50cl',                                                           500.00, 1),
(6, 'Bière Régab',           'Bière locale gabonaise, 65cl bien fraîche',                               2000.00, 1),
(6, 'Coca-Cola / Fanta',     'Canette 33cl bien fraîche',                                               1000.00, 1);