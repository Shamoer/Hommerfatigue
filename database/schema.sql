-- =====================================================================
-- Homme R Fatigué — database
-- Importeer dit bestand in phpMyAdmin in de database "hommerfatigue".
-- LET OP: opnieuw importeren wist alle bestaande inhoud.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS booking_requests, rider_items, login_attempts, users, mixes, gig_translations, gigs,
                     ui_strings, page_translations, pages, languages;
SET FOREIGN_KEY_CHECKS = 1;

-- Talen. Een nieuwe taal = één rij erbij (en daarna de vertalingen invullen).
CREATE TABLE languages (
  code        VARCHAR(5)  NOT NULL PRIMARY KEY,
  name        VARCHAR(50) NOT NULL,
  is_default  TINYINT(1)  NOT NULL DEFAULT 0,
  active      TINYINT(1)  NOT NULL DEFAULT 1,
  sort_order  INT         NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pagina's: de vaste sleutel (page_key) bepaalt het sjabloon.
CREATE TABLE pages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key    VARCHAR(50) NOT NULL UNIQUE,
  in_menu     TINYINT(1)  NOT NULL DEFAULT 1,
  sort_order  INT         NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- De inhoud van elke pagina, per taal.
CREATE TABLE page_translations (
  page_id     INT UNSIGNED NOT NULL,
  lang_code   VARCHAR(5)   NOT NULL,
  slug        VARCHAR(100) NOT NULL DEFAULT '',
  menu_label  VARCHAR(100) NOT NULL,
  title       VARCHAR(200) NOT NULL,
  body        TEXT NULL,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (page_id, lang_code),
  UNIQUE KEY uniq_lang_slug (lang_code, slug),
  CONSTRAINT fk_pt_page FOREIGN KEY (page_id)   REFERENCES pages(id)      ON DELETE CASCADE,
  CONSTRAINT fk_pt_lang FOREIGN KEY (lang_code) REFERENCES languages(code) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kleine vaste teksten van de site (knoppen, tussentitels), per taal.
CREATE TABLE ui_strings (
  string_key  VARCHAR(50) NOT NULL,
  lang_code   VARCHAR(5)  NOT NULL,
  value       TEXT        NOT NULL,
  PRIMARY KEY (string_key, lang_code),
  CONSTRAINT fk_ui_lang FOREIGN KEY (lang_code) REFERENCES languages(code) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gigs: datum en plaats één keer, extra info per taal.
CREATE TABLE gigs (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  gig_date    DATE         NOT NULL,
  start_time  TIME         NULL,
  event_name  VARCHAR(150) NOT NULL DEFAULT '',
  venue       VARCHAR(150) NOT NULL,
  city        VARCHAR(100) NOT NULL DEFAULT '',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_gig_date (gig_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gig_translations (
  gig_id      INT UNSIGNED NOT NULL,
  lang_code   VARCHAR(5)   NOT NULL,
  info        VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (gig_id, lang_code),
  CONSTRAINT fk_gt_gig  FOREIGN KEY (gig_id)    REFERENCES gigs(id)       ON DELETE CASCADE,
  CONSTRAINT fk_gt_lang FOREIGN KEY (lang_code) REFERENCES languages(code) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mixes / sessies (embed-link van Mixcloud of SoundCloud, plus artwork).
CREATE TABLE mixes (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(200) NOT NULL,
  subtitle      VARCHAR(200) NOT NULL DEFAULT '',
  embed_url     VARCHAR(500) NOT NULL DEFAULT '',
  link_url      VARCHAR(500) NOT NULL DEFAULT '',
  cover         VARCHAR(255) NOT NULL DEFAULT '',
  published_at  DATE         NOT NULL,
  active        TINYINT(1)   NOT NULL DEFAULT 1,
  KEY idx_mix_date (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beheerders van de CMS.
CREATE TABLE users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(50)  NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at  DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mislukte inlogpogingen, om raden van wachtwoorden af te remmen.
CREATE TABLE login_attempts (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip            VARCHAR(45) NOT NULL,
  attempted_at  DATETIME    NOT NULL,
  KEY idx_ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Technische rider: wat er in de booth moet staan.
CREATE TABLE rider_items (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quantity    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  item        VARCHAR(150) NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Boekingsaanvragen uit het formulier op de site.
CREATE TABLE booking_requests (
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

-- =====================================================================
-- Startinhoud
-- =====================================================================

INSERT INTO languages (code, name, is_default, active, sort_order) VALUES
  ('nl', 'Nederlands', 1, 1, 1),
  ('en', 'English',    0, 1, 2),
  ('fr', 'Français',   0, 1, 3);

INSERT INTO pages (id, page_key, in_menu, sort_order) VALUES
  (1, 'home',    1, 1),
  (2, 'about',   1, 2),
  (3, 'mixes',   1, 3),
  (4, 'agenda',  1, 4),
  (5, 'booking', 1, 5);

INSERT INTO page_translations (page_id, lang_code, slug, menu_label, title, body) VALUES
  (1, 'nl', '',         'Start',     'Start',
   'Dj en curator uit Brugge. Deep tech, minimal en deep dub house, opgebouwd over lange sets.'),
  (1, 'en', '',         'Home',      'Home',
   'Bruges-based DJ and curator. Deep tech, minimal and deep dub house, built up over long sets.'),
  (1, 'fr', '',         'Accueil',   'Accueil',
   'DJ et curateur basé à Bruges. Deep tech, minimal et deep dub house, construits sur de longs sets.'),

  (2, 'nl', 'over',     'Over',      'Over',
   '## De naam\n\nHomme R Fatigué heeft niets met moe zijn te maken. Het is mijn familienaam, Moerman, in drie stukken geknipt: moe, r, man. Omgedraaid en vertaald naar het Frans wordt dat homme, r, fatigué. Hardop klinkt het begin als Omer.\n\n## Muziek\n\nDeep tech en microhouse, opgebouwd over lange sets. Vaste plek: Bar Oswald in Brugge, met sets van zes uur.'),
  (2, 'en', 'about',    'About',     'About',
   '## The name\n\nHomme R Fatigué has nothing to do with being tired. It is my surname, Moerman, cut into three pieces: moe (Dutch for tired), r, man. Reversed and translated into French, that becomes homme, r, fatigué. Said out loud, the first part sounds like Omer.\n\n## Music\n\nDeep tech and microhouse, built up over long sets. Home base: Bar Oswald in Bruges, with six-hour sets.'),
  (2, 'fr', 'a-propos', 'À propos',  'À propos',
   '## Le nom\n\nHomme R Fatigué n''a rien à voir avec la fatigue. C''est mon nom de famille, Moerman, coupé en trois : moe (fatigué en néerlandais), r, man (homme). Inversé et traduit en français, cela donne homme, r, fatigué. Prononcé à voix haute, le début sonne comme Omer.\n\n## Musique\n\nDeep tech et microhouse, construits sur de longs sets. Port d''attache : le Bar Oswald à Bruges, avec des sets de six heures.'),

  (3, 'nl', 'mixes',    'Mixes',     'Mixes',
   'Mixes en sets, waaronder de reeks per land met deep tech- en microhouse-producers, gestart met België.'),
  (3, 'en', 'mixes',    'Mixes',     'Mixes',
   'Mixes and sets, including the series per country featuring deep tech and microhouse producers, starting with Belgium.'),
  (3, 'fr', 'mixes',    'Mixes',     'Mixes',
   'Mixes et sets, dont la série par pays consacrée aux producteurs deep tech et microhouse, en commençant par la Belgique.'),

  (4, 'nl', 'agenda',   'Agenda',    'Agenda',    ''),
  (4, 'en', 'agenda',   'Agenda',    'Agenda',    ''),
  (4, 'fr', 'agenda',   'Agenda',    'Agenda',    ''),

  (5, 'nl', 'booking',  'Booking',   'Booking',
   'Vul het formulier in of mail rechtstreeks. Je krijgt zo snel mogelijk antwoord.'),
  (5, 'en', 'booking',  'Booking',   'Booking',
   'Fill in the form or send an email directly. You will get an answer as soon as possible.'),
  (5, 'fr', 'booking',  'Booking',   'Booking',
   'Remplissez le formulaire ou écrivez directement. Vous recevrez une réponse dès que possible.');

INSERT INTO ui_strings (string_key, lang_code, value) VALUES
  ('tagline',         'nl', 'deep tech / microhouse / Brugge'),
  ('tagline',         'en', 'deep tech / microhouse / Bruges'),
  ('tagline',         'fr', 'deep tech / microhouse / Bruges'),
  ('latest_mix',      'nl', 'Laatste mix'),
  ('latest_mix',      'en', 'Latest mix'),
  ('latest_mix',      'fr', 'Dernier mix'),
  ('upcoming',        'nl', 'Komende gigs'),
  ('upcoming',        'en', 'Upcoming gigs'),
  ('upcoming',        'fr', 'Prochaines dates'),
  ('past_gigs',       'nl', 'Voorbije gigs'),
  ('past_gigs',       'en', 'Past gigs'),
  ('past_gigs',       'fr', 'Dates passées'),
  ('full_agenda',     'nl', 'Volledige agenda'),
  ('full_agenda',     'en', 'Full agenda'),
  ('full_agenda',     'fr', 'Agenda complet'),
  ('no_gigs',         'nl', 'Momenteel geen gigs gepland.'),
  ('no_gigs',         'en', 'No gigs planned right now.'),
  ('no_gigs',         'fr', 'Aucune date prévue pour le moment.'),
  ('embed_soon',      'nl', 'mix volgt binnenkort'),
  ('embed_soon',      'en', 'mix coming soon'),
  ('embed_soon',      'fr', 'mix bientôt disponible'),
  ('book_title',      'nl', 'Boeken?'),
  ('book_title',      'en', 'Booking?'),
  ('book_title',      'fr', 'Réserver ?'),
  ('book_button',     'nl', 'Neem contact op'),
  ('book_button',     'en', 'Get in touch'),
  ('book_button',     'fr', 'Me contacter'),
  ('footer_location', 'nl', 'Brugge, België'),
  ('footer_location', 'en', 'Bruges, Belgium'),
  ('footer_location', 'fr', 'Bruges, Belgique'),
  ('not_found_title', 'nl', 'Pagina niet gevonden'),
  ('not_found_title', 'en', 'Page not found'),
  ('not_found_title', 'fr', 'Page introuvable'),
  ('not_found_text',  'nl', 'Deze pagina bestaat niet (meer).'),
  ('not_found_text',  'en', 'This page does not exist (anymore).'),
  ('not_found_text',  'fr', 'Cette page n''existe pas (ou plus).');

INSERT INTO ui_strings (string_key, lang_code, value) VALUES
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


-- Voorbeeldgigs: pas aan of verwijder later via de CMS.
INSERT INTO gigs (id, gig_date, start_time, event_name, venue, city) VALUES
  (1, '2026-12-05', '20:00:00', '', 'Bar Oswald', 'Brugge'),
  (2, '2026-10-24', '20:00:00', '', 'Bar Oswald', 'Brugge');

INSERT INTO gig_translations (gig_id, lang_code, info) VALUES
  (1, 'nl', 'Set van 6 uur'), (1, 'en', '6-hour set'), (1, 'fr', 'Set de 6 heures'),
  (2, 'nl', 'Set van 6 uur'), (2, 'en', '6-hour set'), (2, 'fr', 'Set de 6 heures');

INSERT INTO mixes (title, subtitle, embed_url, published_at) VALUES
  ('Nuit Blanche', 'Mix', '', '2026-10-01');

-- Voorbeeld-rider: pas aan via de database tot de CMS dit kan.
INSERT INTO rider_items (quantity, item, sort_order) VALUES
  (2, 'Pioneer CDJ-3000', 1),
  (1, 'Pioneer DJM-V10', 2),
  (2, 'Booth monitors Genelec', 3);
