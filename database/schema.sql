CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('Admin', 'Cliente', 'Driver') NOT NULL DEFAULT 'Cliente',
    telefono VARCHAR(40) NULL,
    indirizzo VARCHAR(255) NULL,
    iban VARCHAR(34) NULL,
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE spedizioni (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codice VARCHAR(40) NOT NULL UNIQUE,
    id_cliente INT UNSIGNED NOT NULL,
    mittente JSON NOT NULL,
    destinatario JSON NOT NULL,
    peso DECIMAL(8,2) NOT NULL,
    dimensioni VARCHAR(80) NULL,
    tipo_servizio VARCHAR(80) NOT NULL,
    assicurazione DECIMAL(8,2) DEFAULT 0,
    stato ENUM('In attesa', 'In transito', 'Consegnata', 'Annullata') NOT NULL DEFAULT 'In attesa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ritiri (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNSIGNED NOT NULL,
    indirizzo JSON NOT NULL,
    data_ritiro DATETIME NOT NULL,
    fascia_oraria VARCHAR(60) NOT NULL,
    stato ENUM('Richiesto', 'Accettato', 'Ritirato', 'In magazzino') DEFAULT 'Richiesto',
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tracking (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_spedizione INT UNSIGNED NOT NULL,
    stato VARCHAR(80) NOT NULL,
    note TEXT NULL,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_spedizione) REFERENCES spedizioni(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fatture (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_spedizione INT UNSIGNED NOT NULL,
    totale DECIMAL(10,2) NOT NULL,
    iva DECIMAL(10,2) NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_spedizione) REFERENCES spedizioni(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_utente INT UNSIGNED NOT NULL,
    id_spedizione INT UNSIGNED NULL,
    messaggio TEXT NOT NULL,
    stato ENUM('Aperto', 'In attesa', 'Chiuso') DEFAULT 'Aperto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_spedizione) REFERENCES spedizioni(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chiave VARCHAR(120) NOT NULL UNIQUE,
    valore TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notification_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_utente INT UNSIGNED NULL,
    canale ENUM('email', 'sms', 'toast') NOT NULL,
    payload JSON NOT NULL,
    stato ENUM('Inviata', 'Errore') NOT NULL DEFAULT 'Inviata',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
