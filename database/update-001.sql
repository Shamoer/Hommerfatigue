-- =====================================================================
-- Update 001 — voor een database die al met de OUDE schema.sql gemaakt is.
-- Voegt de nieuwe kolommen, tabellen en teksten toe zonder je inhoud te wissen.
-- Eén keer importeren in phpMyAdmin. Een nieuwe, lege database? Gebruik dan schema.sql.
-- =====================================================================

SET NAMES utf8mb4;

ALTER TABLE gigs
  ADD COLUMN start_time TIME NULL AFTER gig_date,
  ADD COLUMN event_name VARCHAR(150) NOT NULL DEFAULT '' AFTER start_time;

ALTER TABLE mixes
  ADD COLUMN subtitle VARCHAR(200) NOT NULL DEFAULT '' AFTER title,
  ADD COLUMN link_url VARCHAR(500) NOT NULL DEFAULT '' AFTER embed_url,
  ADD COLUMN cover    VARCHAR(255) NOT NULL DEFAULT '' AFTER link_url;

CREATE TABLE IF NOT EXISTS rider_items (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quantity    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  item        VARCHAR(150) NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS booking_requests (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(190) NOT NULL,
  organisation  VARCHAR(150) NOT NULL DEFAULT '',
  event_date    DATE         NULL,
  venue         VARCHAR(150) NOT NULL DEFAULT '',
  message       TEXT         NOT NULL,
  lang_code     VARCHAR(5)   NOT NULL,
  ip            VARCHAR(45)  NOT NULL,
  handled       TINYINT(1)   NOT NULL DEFAULT 0,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_br_ip_time (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rider_items (quantity, item, sort_order) VALUES
  (2, 'Pioneer CDJ-3000', 1),
  (1, 'Pioneer DJM-V10', 2),
  (2, 'Booth monitors Genelec', 3);

-- Nieuwe vaste teksten (bestaande teksten blijven ongemoeid).
INSERT IGNORE INTO ui_strings (string_key, lang_code, value) VALUES
  ('skip_link',       'nl', 'Naar de inhoud'),
  ('skip_link',       'en', 'Skip to content'),
  ('skip_link',       'fr', 'Aller au contenu'),
  ('bio',             'nl', 'Biografie'),
  ('bio',             'en', 'Biography'),
  ('bio',             'fr', 'Biographie'),
  ('rider',           'nl', 'Technische rider'),
  ('rider',           'en', 'Technical rider'),
  ('rider',           'fr', 'Fiche technique'),
  ('latest_sessions', 'nl', 'Laatste sessies'),
  ('latest_sessions', 'en', 'Latest sessions'),
  ('latest_sessions', 'fr', 'Dernières sessions'),
  ('all_sessions',    'nl', 'Alle sessies'),
  ('all_sessions',    'en', 'All sessions'),
  ('all_sessions',    'fr', 'Toutes les sessions'),
  ('listen',          'nl', 'Beluisteren'),
  ('listen',          'en', 'Listen'),
  ('listen',          'fr', 'Écouter'),
  ('booking_info',    'nl', 'Boekingsinfo'),
  ('booking_info',    'en', 'Booking info'),
  ('booking_info',    'fr', 'Infos booking'),
  ('form_title',      'nl', 'Aanvraag'),
  ('form_title',      'en', 'Request'),
  ('form_title',      'fr', 'Demande'),
  ('f_name',          'nl', 'Naam'),
  ('f_name',          'en', 'Name'),
  ('f_name',          'fr', 'Nom'),
  ('f_email',         'nl', 'E-mail'),
  ('f_email',         'en', 'Email'),
  ('f_email',         'fr', 'E-mail'),
  ('f_organisation',  'nl', 'Organisatie / zaak'),
  ('f_organisation',  'en', 'Organisation / venue'),
  ('f_organisation',  'fr', 'Organisation / lieu'),
  ('f_date',          'nl', 'Datum van het event'),
  ('f_date',          'en', 'Event date'),
  ('f_date',          'fr', 'Date de l''événement'),
  ('f_venue',         'nl', 'Locatie en stad'),
  ('f_venue',         'en', 'Venue and city'),
  ('f_venue',         'fr', 'Lieu et ville'),
  ('f_message',       'nl', 'Bericht'),
  ('f_message',       'en', 'Message'),
  ('f_message',       'fr', 'Message'),
  ('f_optional',      'nl', 'optioneel'),
  ('f_optional',      'en', 'optional'),
  ('f_optional',      'fr', 'facultatif'),
  ('f_send',          'nl', 'Verstuur aanvraag'),
  ('f_send',          'en', 'Send request'),
  ('f_send',          'fr', 'Envoyer la demande'),
  ('f_sent',          'nl', 'Bedankt, je aanvraag is verstuurd. Je hoort snel iets.'),
  ('f_sent',          'en', 'Thanks, your request has been sent. You will hear back soon.'),
  ('f_sent',          'fr', 'Merci, votre demande a été envoyée. Vous aurez bientôt une réponse.'),
  ('f_err_required',  'nl', 'Vul je naam, een geldig e-mailadres en een bericht in.'),
  ('f_err_required',  'en', 'Please fill in your name, a valid email address and a message.'),
  ('f_err_required',  'fr', 'Indiquez votre nom, une adresse e-mail valide et un message.'),
  ('f_err_limit',     'nl', 'Er zijn al een paar aanvragen verstuurd. Probeer het later opnieuw of mail rechtstreeks.'),
  ('f_err_limit',     'en', 'A few requests have already been sent. Please try again later or email directly.'),
  ('f_err_limit',     'fr', 'Plusieurs demandes ont déjà été envoyées. Réessayez plus tard ou écrivez directement.'),
  ('f_err_slow',      'nl', 'Dat ging wel erg snel. Wacht een paar seconden en verstuur opnieuw.'),
  ('f_err_slow',      'en', 'That was very fast. Wait a few seconds and send again.'),
  ('f_err_slow',      'fr', 'C''était très rapide. Attendez quelques secondes et renvoyez.');
