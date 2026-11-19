# Homme R Fatigué — website

Meertalige dj-website (NL / EN / FR) in PHP en MySQL, met een eigen CMS in `beheer/`.
Stijl: monochroom en brutalistisch, opgebouwd zoals de EPK. CSS wordt geschreven in SCSS.

## Lokaal installeren (XAMPP)

1. Zet deze map in `htdocs` als `hommerfatigue`.
2. Kopieer `config.example.php` naar `config.php` en vul je gegevens in.
3. Database:
   - **Nieuwe, lege database:** maak `hommerfatigue` aan (utf8mb4_unicode_ci) en importeer `database/schema.sql`.
     Opnieuw importeren wist alles.
   - **Database van de vorige versie:** importeer één keer `database/update-001.sql`. Je inhoud blijft staan.
4. Open http://localhost/hommerfatigue
5. Eerste keer: open http://localhost/hommerfatigue/beheer/setup.php en maak je beheerdersaccount aan.

## CSS aanpassen (SCSS)

Eenmalig: installeer Node.js en voer in deze map `npm install` uit.

- `npm run watch` — tijdens het werken: elke keer je een `.scss` opslaat, wordt `css/main.css` opnieuw gemaakt.
- `npm run build` — compacte versie maken voor je online zet.

Commit `css/main.css` altijd mee: de server heeft geen Node nodig, hij gebruikt gewoon dat bestand.

```
scss/
  main.scss            hoofdbestand, haalt de rest op
  _tokens.scss         kleuren, fonts, maten, lijnen  ← hier pas je de stijl aan
  _mixins.scss         herbruikbare stukjes (label, display, korrel, breekpunten)
  _fonts.scss          lettertypes uit /fonts
  _base.scss           basis voor de hele pagina
  _typography.scss     titels en lopende tekst
  components/          één bestand per onderdeel (header, hero, lijsten, formulier, ...)
  components/_motion   animaties bij het laden en de kaart
```

Beweging: snelheden en curves staan in `_tokens.scss` (`$speed`, `$ease`, `$pop`).
In `_mixins.scss` zitten `transition`, `underline-grow` (uitschuivende onderlijn) en `pop` (opspringen met harde schaduw).
Wie op zijn toestel "minder beweging" heeft aangezet, krijgt geen animaties.

## Online zetten (netcup)

- In `config.php`: `BASE_URL = ''`, `DEBUG = false` en je echte mailadressen.
- In `.htaccess`: `RewriteBase /`.
- Upload alles behalve `node_modules/`, `scss/` en `package.json` (mag wel, wordt geblokkeerd).
- Meteen `/beheer/setup.php` openen om het account aan te maken.

## Opbouw

- `index.php` — ontvangt elk adres, kiest taal en pagina, verwerkt het boekingsformulier
- `src/` — databaseverbinding, taal-, inhouds- en boekingsfuncties
- `templates/` — de HTML; bestanden met `_` zijn herbruikbare stukken
- `beheer/` — de CMS: pagina's per taal, gigs, sessies (met artwork), rider en boekingsaanvragen
- `database/` — `schema.sql` (alles nieuw) en `update-001.sql` (bestaande database bijwerken)
- `fonts/` — Chakra Petch en IBM Plex Mono, lokaal (geen Google Fonts nodig)
- `img/` — portret; artwork van sessies komt via de CMS in `img/sessies/` (die map moet schrijfbaar zijn)

`src/`, `templates/` en `database/` hebben elk een `.htaccess` die rechtstreekse toegang blokkeert.

## In de CMS

- **Pagina's** — titel, menunaam, adres en tekst per taal.
- **Gigs** — datum, uur, eventnaam, zaak, stad en extra info per taal. Voorbije gigs verschuiven vanzelf naar "voorbij".
- **Sessies** — titel, datum, speler (plak gewoon de link van Mixcloud of SoundCloud), extra link en artwork.
  Artwork wordt automatisch vierkant bijgesneden, verkleind en opnieuw opgeslagen als JPG.
- **Rider** — toestellen met aantal, volgorde met ↑ ↓.
- **Aanvragen** — boekingsaanvragen uit het formulier, af te vinken als behandeld.
