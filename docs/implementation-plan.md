# Coresuite Parcel · Piano di Implementazione

## Obiettivi immediati
- Definire flussi di autenticazione completi con registrazione, reset password via token e gestione ruoli (Admin, Cliente, Driver).
- Implementare viste dashboard differenziate per ruolo con controlli di accesso basati su `require_role`.
- Completare CRUD spedizioni con stati e filtri avanzati lato UI.
- Integrare gestione ritiri con calendario driver e aggiornamento stato sequenziale.
- Abilitare timeline tracking pubblica con aggiornamenti real-time (AJAX polling o WebSocket da valutare in seguito).

## Integrazioni tecniche
- Configurare build Tailwind CSS tramite CLI o Vite per generare CSS ottimizzato in `assets/css/build.css`.
- Integrare libreria QR Code (es. `endroid/qr-code`) e generatore codice a barre (es. `picqer/php-barcode-generator`).
- Scegliere libreria PDF (TCPDF o FPDF) e definire template etichette A6 in `modules/labels.php`.
- Preparare invio email/SMS via provider esterni (Mailgun, Twilio) con coda notifiche.

## Database & migrazioni
- Scrivere script SQL di creazione tabelle basate sul modello fornito.
- Valutare uso di tool migrazioni (Phinx o Laravel Migrations a parte) per versioning DB (facoltativo).

## Sicurezza & audit
- Aggiungere logging attività utente con tabella dedicata (`activity_log`).
- Applicare token CSRF su tutti i form e limitare rate login.
- Validare upload documenti (tipi file, dimensioni) in cartella `uploads/` con storage protetto.

## Fasi successive
- Dashboard analytics con grafici (Chart.js) e report PDF/CSV in `modules/reports.php`.
- API REST per app mobile e integrazione corrieri (GLS, SDA, DHL) con chiavi configurabili via `settings`.
- Notifiche push browser e supporto stampanti termiche per etichette.
