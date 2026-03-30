SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(191) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    description TEXT NULL,
    seo_title VARCHAR(70) NULL,
    seo_description VARCHAR(160) NULL,
    status ENUM('active', 'hidden') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_categories_slug (slug),
    UNIQUE KEY uq_categories_name (name),
    KEY idx_categories_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    author_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) NULL,
    image_alt VARCHAR(255) NULL,
    meta_title VARCHAR(70) NULL,
    meta_description VARCHAR(160) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_articles_slug (slug),
    KEY idx_articles_status_published_at (status, published_at),
    KEY idx_articles_category_status (category_id, status),
    KEY idx_articles_author (author_id),
    FULLTEXT KEY ft_articles_search (title, excerpt, content),
    CONSTRAINT fk_articles_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT fk_articles_author
        FOREIGN KEY (author_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, email, password, role)
VALUES (
    'admin',
    'admin@example.com',
    '$2y$12$3mmRmuJICOrAHMUiy2jdOuSRFKWQY69KGFgB2DZgGouFfonHnqO86',
    'admin'
)
ON DUPLICATE KEY UPDATE
    email = VALUES(email),
    password = VALUES(password),
    role = VALUES(role);

INSERT INTO categories (name, slug, description, seo_title, seo_description, status)
VALUES
    (
        'Analyses',
        'analyses',
        'Analyses de fond et de contexte sur le conflit.',
        'Analyses du conflit Iran-Irak',
        'Dossiers et analyses detaillees sur les dynamiques du conflit Iran-Irak.',
        'active'
    ),
    (
        'Geopolitique',
        'geopolitique',
        'Equilibres regionaux, alliances et rapports de force.',
        'Geopolitique du conflit',
        'Comprendre les rapports de force regionaux autour de la guerre Iran-Irak.',
        'active'
    ),
    (
        'Militaire',
        'militaire',
        'Operations, strategies et capacites militaires.',
        'Volet militaire de la guerre',
        'Chroniques des operations militaires et des evolutions strategiques.',
        'active'
    ),
    (
        'Diplomatie',
        'diplomatie',
        'Mediations, resolutions internationales et canaux de negociation.',
        'Diplomatie et mediation',
        'Suivi des efforts diplomatiques autour du conflit Iran-Irak.',
        'active'
    ),
    (
        'Economie',
        'economie',
        'Impact economique, energie et commerce en temps de guerre.',
        'Economie de guerre',
        'Analyse des impacts economiques et energetiques lies au conflit.',
        'active'
    )
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    seo_title = VALUES(seo_title),
    seo_description = VALUES(seo_description),
    status = VALUES(status);
