# Coresuite Parcel

Web app gestionale per spedizioni B2B/B2C sviluppata in PHP nativo con MySQL, Tailwind CSS e JavaScript vanilla. La piattaforma offre dashboard multi-ruolo, tracking pubblico, gestione ritiri, ticketistica e fatturazione automatica.

## Stack
- PHP 8+
- MySQL 8+
- Tailwind CSS 3
- JavaScript vanilla
- Node.js (tooling Tailwind)

## Struttura principale
```
assets/         Risorse statiche (Tailwind, JS, immagini)
includes/       Bootstrap condiviso (DB, auth, funzioni)
modules/        Moduli applicativi (spedizioni, ritiri, fatture, etichette)
pdf/            Output PDF generati (etichette, fatture)
uploads/        Documenti caricati dagli utenti
index.php       Landing page pubblica
login.php       Autenticazione utenti
dashboard.php   Dashboard multi-ruolo
tracking.php    Tracking pubblico
```

## Setup rapido
1. Popola il file `.env` con le variabili `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_CHARSET`.
2. Installa le dipendenze front-end:
   ```powershell
   npm install
   npm run build
   ```
3. Esegui la migrazione del database:
   ```powershell
   php bin/migrate.php
   ```
4. Imposta il tuo virtual host / server PHP (Apache, Nginx o `php -S`) puntando alla cartella del progetto.

## Script npm
- `npm run dev`: genera il CSS Tailwind in modalità watch (task VS Code disponibile).
- `npm run build`: produce il CSS ottimizzato in `assets/css/build.css`.

## Migrazione database
- `php bin/migrate.php`: applica lo schema definito in `database/schema.sql` al database configurato nel `.env`.

## Prossimi passi
Consulta `docs/implementation-plan.md` per roadmap funzionale, integrazioni e attività di sicurezza.
