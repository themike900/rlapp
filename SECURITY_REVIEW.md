# Sicherheitsreview rlapp

Stand: 12. November 2026. Geprüft wurde der Quellstand dieses Repositories (Laravel-11-Anwendung, öffentliche Webauslieferung). Es wurde kein Anwendungscode geändert. Die laufende Serverkonfiguration (`.env`, Document Root, Webserver vor dem PHP-Prozess) ist von hier aus nicht einsehbar; solche Punkte sind als **zu prüfen** markiert.

Bewertet wurde nur, was sich im Code tatsächlich ausnutzen lässt. Klassische SQL-Injection in den Anwendungsabfragen wurde nicht gefunden (Query Builder mit Bindings). Passwörter der Backend-Benutzer laufen über den Laravel-Cast `hashed` (bcrypt). Der Login ist auf 5 Versuche gedrosselt und erneuert die Session. Web-Formulare im Backend senden ein CSRF-Token. `public/.htaccess` schaltet Directory-Listing aus (`Options -Indexes`).

## Gesamtrisiko

**Kritisch.** Die Anwendung ist die interne Aktivitätenplanung (Mitglieder, Mobilnummern, E-Mail, SMS, Fahrten) und hängt an der öffentlichen Mitgliederseite rlweb. Mehrere zustandsändernde Endpunkte sind ohne Anmeldung erreichbar. Dazu gehört die Schnittstelle, über die rlweb Anmeldungen schreibt, ein Excel-Import, der Gruppen und Handynummern überschreibt, und HTTP-Routen, die `schedule:run` und `queue:work` ausführen.

Zusätzlich kann sich jede Person selbst ein Backend-Konto anlegen. Es gibt keine Rollen. Jedes Konto darf Fahrten absagen, E-Mails und SMS auslösen und die E-Mail-Vorlagen ändern. Diese Vorlagen werden mit `Blade::render` ausgeführt. Zusammen mit dem offenen Queue-Endpunkt ist das aus dem Internet bis zur Codeausführung auf dem Server durchreichbar.

Solange die Instanz so aus diesem Stand deployt ist, sollte sie nicht als geschütztes Vereinsbackend gelten.

## Bezug zu rlweb

In diesem Repo gibt es kein `include` von rlweb, keine zweite Datenbankverbindung und keine gemeinsame Session. Die Kopplung ist HTTP, plus dieselbe fachliche `webid`:

| Richtung | Stelle in rlapp | Erwartung an rlweb |
| --- | --- | --- |
| Listen | `POST /api/list` | rlweb schickt `webid`, Name, Vorname, E-Mail, `list_type` |
| Details | `GET /api/details/{webid}/{actionid}` | rlweb holt Anmeldeoptionen und Teilnehmer als HTML |
| Anmeldung | `POST /api/rlreg` | Formular, danach Redirect auf `https://{host}/intern/details` |
| Gäste | `POST /api/gstreg` | gleicher Redirect |
| PDFs | `GET /rl/fahrtenblatt/{actionId}`, `GET /rl/fahrtenliste/{base64}` | Links aus der Mitgliederseite |
| Abschluss | `GET /api/cron` | setzt Fahrten auf geschlossen |

`routes/api.php` ist in `routes/web.php` auskommentiert, wird aber in `bootstrap/app.php` (Zeilen 8–12) als API geladen. Die Routen sind damit unter `/api/...` aktiv, mit der API-Middleware (Drosselung, keine Session, kein CSRF, kein Login).

In rlweb sollte zusätzlich geprüft werden:

- ob `webid` nur eine fortlaufende Nummer ist und im Browser sichtbar ist
- ob `/api/list`, `/api/rlreg` und `/api/gstreg` vom Browser (Formular) oder serverseitig aufgerufen werden
- ob die HTML-Felder `members.captain`, `members.crew`, `members.service`, `members.participants` ungefiltert ins DOM geschrieben werden
- woher `host` kommt und ob rlweb den Wert aus der Anfrage übernimmt
- ob rlweb und rlapp Cookies auf derselben Domain teilen
- ob rlweb eigene Kopien von Zugangsdaten, SQL oder Datei-Includes hat, die dieselbe `members`-Logik wiederholen

## Befunde

### K1. Öffentliche API schreibt und liest Mitgliedsdaten ohne Authentifizierung

**Schweregrad:** kritisch

**Dateien:**

- `routes/api.php`, Zeilen 17–29
- `bootstrap/app.php`, Zeilen 8–12
- `app/Http/Controllers/Api/ApiListController.php`, Zeilen 37–110 und 125–129
- `app/Http/Controllers/Api/ApiRegController.php`, Zeilen 148–231
- `app/Http/Controllers/Api/ApiGuestRegController.php`, Zeilen 141–217
- `app/Http/Controllers/Api/ApiDetailsController.php`, Zeilen 33–36, 151–163, 486–587
- `app/Http/Requests/ApiRequest.php`, Zeilen 16–18 (wird von den Controllern nicht benutzt; `authorize()` gäbe ohnehin `true` zurück)

**Ausnutzung:**

`POST /api/list` sucht ein Mitglied erst über `webid`, dann über die E-Mail, dann über Vor- und Nachname. Wird jemand gefunden, schreibt der Endpunkt die mitgelieferte `webid` in den Datensatz und, wenn die alte `webid` mindestens 10000 ist, auch in `action_members`. Wer E-Mail oder Name einer Person kennt, hängt deren Anmeldungen an eine selbst gewählte `webid`. Existiert niemand, legt der Endpunkt ein neues Mitglied an. Doppelte Sätze zur selben `webid` werden gelöscht.

`POST /api/rlreg` meldet eine beliebige `webid` zu einer beliebigen Fahrt an oder ab. Die Crew-Anmeldung prüft nicht, ob die Person in der Gruppe `cr` oder `sv` ist. `anm_opt=bereit_cr` reicht. Die Abmeldung löscht den Datensatz auch dann, wenn die Fahrt geschlossen ist (der entsprechende TODO-Kommentar ist offen). Gäste dieser Anmeldung werden mitgelöscht.

`POST /api/gstreg` hängt einen Gast an eine beliebige `reg_id` oder löscht einen Gast über seine numerische `id`.

`GET /api/details/{webid}/{actionid}` liefert Namen, Anmeldezustand, Gästeliste, interne Zähler und das Flag `debug`. Derselbe GET schreibt `actions.ac_reg_state_tn` auf `tnon` oder `tnoff`. Ein Aufruf oder ein vorgeschalteter Fetch kann die Teilnehmeranmeldung öffnen oder schließen.

Ein unbekannter `list_type` trifft in `ApiListController` Zeile 125 ein `match` ohne `default` und wirft eine Exception. Bei `APP_DEBUG=true` (**zu prüfen**, ob das in der Produktion an ist) zeigt Laravel den Stacktrace.

**Behebung:**

Die API darf Identitäten nicht aus dem POST-Body glauben. rlweb sollte serverseitig ein kurzlebiges, signiertes Token erzeugen (webid, Ablaufzeit). rlapp prüft Signatur und Ablauf und lässt `host` nur gegen eine feste Liste zu. Gruppenrechte müssen in rlapp erneut geprüft werden. `GET /api/details` darf keine Zustände schreiben.

```php
$secret = (string) config('services.rlweb.token');
$sig = (string) $request->header('X-RL-Signature');
$expected = hash_hmac('sha256', $request->getContent(), $secret);
if ($secret === '' || !hash_equals($expected, $sig)) {
    abort(401);
}

$allowedHosts = ['www.example.org']; // echte rlweb-Hosts
if (!in_array($request->input('host'), $allowedHosts, true)) {
    abort(400);
}
```

Zusätzlich: Anmeldung nur, wenn `members.groups` die Gruppe enthält; Abmeldung nur in den Zuständen, die fachlich noch offen sind; Gäste nur zur `reg_id` des signierten Mitglieds.

### K2. Selbstregistrierung ohne Rollen, jedes Konto ist ein volles Backend

**Schweregrad:** kritisch

**Dateien:**

- `routes/auth.php`, Zeilen 14–18
- `app/Http/Controllers/Auth/RegisteredUserController.php`, Zeilen 30–48
- `app/Models/User.php`, Zeile 5 (`MustVerifyEmail` ist auskommentiert) und Zeilen 20–25
- `app/Livewire/LayoutWrapper.php`, Zeilen 12–18, mit dem Menü in `resources/views/livewire/layout-wrapper.blade.php`, Zeilen 8–16
- `app/Livewire/Pages/RlMembersImport.php`, Zeilen 16–19 (Test-SMS an eine fest eingetragene `webid`)
- `app/Livewire/AcCancelModal.php`, Zeilen 51–98 (Absage, E-Mail, SMS, Löschen der Anmeldungen)

**Ausnutzung:**

`GET/POST /register` ist für Gäste offen. Nach dem Anlegen ist die Person sofort eingeloggt und landet auf `/rl/pages`. Es gibt kein Rollenfeld und keine Policy. Das Menü bietet jedem Konto Aktivitäten, Crew, Teilnehmer, E-Mail-Vorlagen, die Mitgliederliste und den Import. Darüber sind Namen, E-Mails und Mobilnummern lesbar, Fahrten absagbar und kostenpflichtige SMS auslösbar. Die Test-SMS im Import geht an ein festes Mitglied, sobald ein eingeloggtes Konto den Knopf drückt.

Die Route `GET /members/import` verlangt `verified`, die eigentliche Arbeitsoberfläche aber nicht. E-Mail-Bestätigung schützt das Backend nicht.

**Behebung:**

Register-Routen entfernen. Bestehende Benutzer prüfen und unbekannte Konten sperren (**zu prüfen** in der Produktionstabelle `users`). Danach ein explizites Recht, das an jeder Livewire-Aktion und an jedem Controller hängt, nicht nur an der Seitenroute.

```php
// routes/auth.php: die beiden register-Routen entfernen.

// bootstrap/app.php, bis ein echtes Rollenmodell da ist:
->withMiddleware(function (Middleware $middleware) {
    $middleware->redirectGuestsTo('/login');
})
```

`User` sollte `MustVerifyEmail` nur dann implementieren, wenn Registrierung überhaupt bleiben soll. Für ein Vereinsbackend ist das Anlegen der Konten durch eine verantwortliche Person der passende Weg. `RlMembersImport::testSMS()` entfernen oder auf eine lokale Umgebung begrenzen.

### K3. HTTP-Cron führt Scheduler und Queue ohne Geheimnis aus

**Schweregrad:** kritisch

**Dateien:**

- `routes/web.php`, Zeilen 38–60
- `routes/api.php`, Zeile 29
- `app/Http/Controllers/Api/ApiCronController.php`, Zeilen 13–23
- `routes/console.php`, Zeilen 8–25 (Logrotation), 28–141 (E-Mails an Schiffsführung, Service, Schatzmeister), 145–179 (Fahrten auf `df`, Mail an den Schatzmeister)
- `config/app.php`, Zeile 126 (`CRON_SECRET` fällt auf `''` zurück)

**Ausnutzung:**

`GET /cron/schedule` und `GET /cron/queue` prüfen kein Token. Der erste Aufruf startet `schedule:run`. Steht die Uhrzeit im Fenster der geplanten Tasks, versendet die Anwendung echte E-Mails, setzt Fahrten auf durchgeführt und benennt bzw. löscht Logdateien. Der zweite Aufruf arbeitet die Queue im Webprozess ab, also alle dort liegenden E-Mail- und SMS-Jobs, und kann den PHP-Prozess bis zum Timeout belegen. Beides sind GET-Requests: eine fremde Seite kann sie per `<img>` auslösen.

`GET /cron/schedule/{token}` und `GET /cron/queue/{token}` vergleichen das Token mit `!==` statt `hash_equals`. Das Geheimnis steht in der URL und damit in Access-Logs, Referrern und Browser-Historie. Ist `CRON_SECRET` leer, ist das kein Schutz für die beiden Routen ohne Token; die bleiben offen.

`GET /api/cron` ist ebenfalls ohne Anmeldung und setzt alle offenen Fahrten der Typen `vf`, `af`, `gfx`, `gfm`, `uf` mit Datum in den nächsten fünf Tagen auf `gs`.

**Behebung:**

Die vier GET-Routen und `GET /api/cron` entfernen. Auf dem Webspace den System-Cron verwenden: `php artisan schedule:run` einmal pro Minute, Queue über `queue:work` als Dienst oder ebenfalls per CLI. Wenn der Hoster nur HTTP-Cron kann: POST, Bearer-Token, `hash_equals`, und bei leerem Geheimnis hart abbrechen.

```php
Route::post('/cron/schedule', function (Request $request) {
    $secret = (string) config('app.cron_secret');
    $given = (string) $request->bearerToken();
    if ($secret === '' || !hash_equals($secret, $given)) {
        abort(403);
    }
    Artisan::call('schedule:run');
    return 'OK';
});
```

Dieselbe Prüfung für die Queue, mit `--stop-when-empty` und `--max-time`. Das Schließen der Fahrten gehört in den Scheduler, nicht in eine öffentliche URL.

### K4. Mitglieder-Import ohne Anmeldung überschreibt Gruppen und Mobilnummern

**Schweregrad:** kritisch

**Dateien:**

- `routes/web.php`, Zeilen 24–29
- `app/Http/Controllers/ImportController.php`, Zeilen 11–17
- `app/Imports/MembersImport.php`, Zeilen 20–79

**Ausnutzung:**

`GET /members/import` liegt hinter `auth`. `POST /import` nicht. Die Web-Middleware verlangt ein CSRF-Token, das sich jede Person über die eigene Session auf der Login-Seite holt. Eine Anmeldung ist dafür nicht nötig.

Die Datei muss als xlsx/xls durchgehen. `MembersImport` sucht das Mitglied über `mv_id`, sonst über E-Mail und Vorname, sonst über Vor- und Nachname, und überschreibt dann `mv_id`, `groups` und `mobile`. Die Spalte `Mannschaft` wird auf Kürzel wie `cr`, `sf`, `vs` abgebildet. Damit lässt sich die Gruppenzugehörigkeit bestehender Mitglieder ändern und die SMS-Nummer auf ein fremdes Handy legen. Neue Zeilen werden als Mitglieder angelegt.

Die View nennt die erwarteten Spalten; dieselben Namen stehen in diesem Repo.

**Behebung:**

```php
Route::post('/import', [ImportController::class, 'importMembers'])
    ->middleware(['auth'])
    ->name('import');
```

Dazu dieselbe Rechteprüfung wie in K2. Den Import auf dem Server zusätzlich auf erwartete Spalten und eine maximale Zeilenzahl begrenzen. Gruppen nur aus einer festen Abbildung übernehmen, unbekannte Werte verwerfen.

### K5. E-Mail- und SMS-Vorlagen werden als Blade-PHP ausgeführt

**Schweregrad:** kritisch

**Dateien:**

- `app/Livewire/Emails/EmailTexts.php`, Zeilen 19–24
- `resources/views/livewire/emails/email-texts.blade.php`, Zeilen 23–30
- `app/Jobs/SendEmail.php`, Zeile 87
- `app/Jobs/SendSms.php`, Zeile 86
- auslösbar über K2 und abgearbeitet über K3 (`GET /cron/queue`)

**Ausnutzung:**

Der Text in `email_templates.text` wird gespeichert und später mit `Blade::render` kompiliert. Blade führt `@php` und PHP-Ausdrücke aus, nicht nur Platzhalter. Jedes Backend-Konto (siehe K2) kann die Vorlage ändern. Der Test-SMS-Knopf oder eine Fahrtabsage legt einen Job in die Queue. `GET /cron/queue` arbeitet die Queue ohne Anmeldung ab. Damit läuft der Vorlageninhalt im Webserver-Benutzer.

Auch ohne die offene Registrierung bliebe das Risiko für jedes Konto bestehen, das die Vorlagen bearbeiten darf, und für eine Session, die über K6 übernommen wurde.

**Behebung:**

Vorlagen nicht kompilieren. Platzhalter als Text ersetzen und den Versand nur mit festem HTML-Grundgerüst bauen.

```php
$emailText = strtr($template->text, [
    '{firstname}' => $member->firstname,
    '{action_name}' => $action->action_name,
    '{action_date}' => $action_date,
    '{captain}' => $sf_name,
    '{cancel_reason}' => $action->cancel_reason,
]);
```

`EmailTexts` darf nur diese Platzhalter speichern. Zeichenfolgen wie `{{`, `{!!` und `@php` beim Speichern abweisen. Bestehende Vorlagen in der Datenbank auf solche Stellen prüfen (**zu prüfen**).

### H1. Fahrtenblatt und Fahrtenliste ohne Anmeldung, Base64 ist keine Zugriffskontrolle

**Schweregrad:** hoch

**Dateien:**

- `routes/web.php`, Zeilen 31–32
- `app/Http/Controllers/FartenblattPdf.php`, Zeilen 16–49 und 92–113
- `app/Http/Controllers/Pdfs/FartenlistePdf.php`, Zeilen 13–22 und 40–103
- `app/Livewire/MembersList/MembersTable.php`, Zeilen 160–161 (Erzeugung des Links)

**Ausnutzung:**

`/rl/fahrtenblatt/{actionId}` nimmt eine numerische Id. Das PDF enthält Namen von Schiffsführung, Crew, Service, Teilnehmern und Gästen sowie die Mobilnummern von Crew und Service.

`/rl/fahrtenliste/{webId}` erwartet Base64 von `webid/zufallszahl`. Die Zufallszahl wird nach dem `explode` nicht geprüft. `base64("123/1")` genügt. Die `webid` ist fortlaufend, die Liste damit durchzählbar. Das PDF zeigt die Fahrten und den Anmeldestand dieser Person. Fehlt das Mitglied, greift der Code auf `$member->groups` zu und wirft eine Exception (bei Debug ein Stacktrace, **zu prüfen**).

**Behebung:**

Keine öffentlichen Integer- oder Base64-Links. rlapp stellt eine kurz signierte URL aus, die rlweb nur nach eigener Anmeldung anzeigt. Middleware `signed` und eine kurze Laufzeit. Mobilnummern nur auf dem Fahrtenblatt, das an die Schiffsführung geht, nicht in einem unauthentifizierten Download.

```php
$url = URL::temporarySignedRoute(
    'rl-fahrtenblatt',
    now()->addMinutes(10),
    ['actionId' => $actionId]
);
```

```php
Route::get('/rl/fahrtenblatt/{actionId}', [FartenblattPdf::class, 'generatePdf'])
    ->middleware('signed')
    ->name('rl-fahrtenblatt');
```

### H2. Gespeicherte HTML-Ausgabe von Namen im Backend und in der API für rlweb

**Schweregrad:** hoch

**Dateien:**

- `resources/views/livewire/ac-view-modal.blade.php`, Zeilen 42–50
- `app/Services/ParticipantsListService.php`, Zeilen 26 und 42–93 (`fullname` ohne Escaping)
- `database/migrations/2025_05_18_184100_change_members_table.php`, Zeilen 15–16 (`fullname` ist `CONCAT(firstname, ' ', name)`)
- `app/Http/Controllers/Api/ApiDetailsController.php`, Zeilen 493–567 (Namen werden mit `<br>` und HTML-Entities verkettet)
- `app/Http/Controllers/FartenblattPdf.php`, Zeilen 49, 68, 104
- `resources/views/layouts/fahrtenblatt.blade.php`, Zeilen 126–127 und 154–159

**Ausnutzung:**

Namen kommen über K1 (`POST /api/list` legt `firstname` und `name` an) und über K4 in die Datenbank. Die Detailansicht im Backend gibt sie mit `{!! !!}` aus. Ein Name mit HTML-Markup läuft im Browser der Person, die die Fahrt öffnet, also in einer Backend-Session. Von dort sind die Aktionen aus K2 und K5 erreichbar.

Dieselbe API baut die Teilnehmerliste bewusst als HTML. Wenn rlweb dieses Fragment ins DOM schreibt, gilt derselbe Angriff für alle Mitglieder, die die Fahrt auf der Webseite öffnen. **Zu prüfen** in rlweb, ob die Felder als HTML oder als Text eingesetzt werden. Die anderen `{!! !!}` in `members-table.blade.php` und `text-input.blade.php` geben nur feste Zeichen oder Blade-Attribute aus und sind hier nicht das Problem. `reg_state_text` im Listen-PDF kommt aus einem festen `match`.

**Behebung:**

Im Backend `{{ }}` verwenden. In der API keine HTML-Fragmente liefern, sondern Arrays von Namen. rlweb escaped beim Rendern. Solange HTML nötig ist, jeden Namen einzeln durch `e()` schicken und nur die eigenen `<br>` dazusetzen.

```blade
Schiffsführer: <b>{{ $members['captain'] ?? '-' }}</b>
```

### H3. SMS-API-Token in der Git-Historie

**Schweregrad:** hoch

**Datei:** `app/Jobs/SendSms.php` (aktueller Stand Zeile 93 nutzt `env('SMSAPI_TOKEN')`)

**Ausnutzung:**

In der Historie wurde der SMSAPI-Token als Argument von `env()` eingecheckt und kurz darauf wieder durch den Variablennamen `SMSAPI_TOKEN` ersetzt. Betroffene Commits: `db93cfc` und `b6ba35c`. Der Wert steht nicht mehr im Arbeitsstand, bleibt aber in `git log` jedes Klons. Ob der Token bei SMSAPI noch gültig ist, ist **zu prüfen**. Bis zur Rotation ist er als bekannt anzusehen. Ein gültiger Token erlaubt SMS-Versand auf Kosten des Vereinskontos.

Der aktuelle Code liest das Geheimnis mit `env()` direkt im Job statt über `config/`. Nach `php artisan config:cache` ist `env()` außerhalb von Config-Dateien leer, der Versand schlägt dann fehl oder läuft ohne Token. Die Antwort von SMSAPI wird in Zeile 102 ins Log geschrieben.

**Behebung:**

Token beim Anbieter ungültig machen und einen neuen nur in der Server-`.env` hinterlegen. Nicht in Git, nicht in Commits, nicht in Logs. Historie neu schreiben entfernt den Wert nicht aus bereits gezogenen Kopien; die Rotation ist der wirksame Schritt.

```php
// config/services.php
'smsapi' => [
    'token' => env('SMSAPI_TOKEN'),
],

// SendSms::handle
$response = Http::withToken(config('services.smsapi.token'))
    ->asForm()
    ->post('https://api.smsapi.com/sms.do', [ /* ... */ ]);
```

Die Response nur mit Statuscode loggen, nicht den Body.

### H4. Livewire 3.5.20 liegt im Bereich von CVE-2025-54068

**Schweregrad:** hoch

**Datei:** `composer.lock`, Paket `livewire/livewire`, Version `v3.5.20`

**Ausnutzung:**

CVE-2025-54068 (GHSA-29cq-5w36-x7w3) betrifft Livewire 3 vor 3.6.4. Beim Hydrieren von Property-Updates kann eine Anfrage ohne Login Kommandos ausführen, wenn eine Komponente auf eine bestimmte Art gemountet ist. Es gibt keinen Workaround, öffentliche Exploits sind beschrieben, die Lücke wurde als bekannte ausgenutzte Schwachstelle geführt.

Ob die Komponenten dieser Anwendung die nötige Konfiguration treffen, ist **zu prüfen**. Die Login-Seite lädt Livewire nicht (`resources/views/layouts/guest.blade.php`). Die Arbeitsoberfläche lädt es (`resources/views/layouts/app.blade.php`, Zeilen 19–23), und viele öffentliche Properties werden vom Client aktualisiert. Die Version allein ist Grund zum Update, auch wenn der konkrete Pfad in dieser App nicht nachgestellt wurde.

**Behebung:**

```bash
composer update livewire/livewire
```

Mindestens 3.6.4, besser der aktuelle 3.x-Stand. Danach eine Anmeldung, einen Seitenwechsel im Menü und eine Speichern-Aktion prüfen.

### M1. Offener Redirect über das Feld `host`

**Schweregrad:** mittel

**Dateien:**

- `app/Http/Controllers/Api/ApiRegController.php`, Zeile 302
- `app/Http/Controllers/Api/ApiGuestRegController.php`, Zeile 217

**Ausnutzung:**

Nach der Anmeldung leitet rlapp den Browser auf `https://` plus den mitgeschickten Host plus `/intern/details` um. Der Host wird nicht gegen rlweb geprüft. Ein Formular oder ein Skript kann `host` auf eine fremde Domain setzen. Die Person, die gerade eine Fahrt buchen wollte, landet auf dieser Domain. Das ist ein Redirect, kein serverseitiger Abruf.

**Behebung:**

Allowlist wie in K1. Alles andere mit 400 ablehnen, nicht umleiten.

### M2. Passwort-Reset ohne Drosselung, unterschiedliche Antworten

**Schweregrad:** mittel

**Dateien:**

- `routes/auth.php`, Zeilen 25–29
- `app/Http/Controllers/Auth/PasswordResetLinkController.php`, Zeilen 35–42

**Ausnutzung:**

`POST /forgot-password` hat kein `throttle`. Die Antwort unterscheidet „Link gesendet“ und „Benutzer unbekannt“. Damit lässt sich prüfen, welche E-Mail-Adressen ein Backend-Konto haben, und der Mailserver lässt sich mit Reset-Mails fluten. Der Login ist gedrosselt, diese Route nicht. `POST /register` ist ebenfalls nicht gedrosselt (relevant, solange K2 nicht entfernt ist).

**Behebung:**

```php
Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('password.email');
```

Immer dieselbe Meldung zurückgeben, unabhängig davon, ob die Adresse existiert.

### M3. Produktionskonfiguration und Document Root

**Schweregrad:** mittel, im schlechten Fall kritisch. **Zu prüfen** auf dem Webspace.

**Dateien:**

- `.env.example`, Zeilen 2–4 und 29–32 (`APP_ENV=local`, `APP_DEBUG=true`, `SESSION_ENCRYPT=false`)
- `config/app.php`, Zeile 42
- `config/session.php`, Zeilen 50 und 172 (`SESSION_ENCRYPT` und `SESSION_SECURE_COOKIE` standardmäßig aus)
- `public/.htaccess` (Schutz nur unterhalb von `public/`)
- kein `.htaccess` im Projektroot, das `.env`, `storage/` oder `vendor/` verbietet

**Ausnutzung:**

Liegt der Document Root auf dem Projektverzeichnis und nicht auf `public/`, sind `.env`, `storage/logs` und eine SQLite-Datei per URL ladbar. In der `.env` stehen dann `APP_KEY`, Datenbankpasswort und `SMSAPI_TOKEN`. Das wäre kritisch. Ob der Hoster so eingerichtet ist, ist **zu prüfen**.

Unabhängig davon: `APP_DEBUG=true` macht aus den Exceptions in K1 und H1 eine Informationsquelle. Ein Session-Cookie ohne `Secure` wird auf einem HTTPS-Auftritt bei einem Downgrade mitgeschickt. `SESSION_ENCRYPT=false` lässt Session-Inhalte in der Datenbank im Klartext (Login-Zustand, nicht das Passwort).

`public/.htaccess` setzt `Options +FollowSymLinks`. Indexes sind aus. Es fehlen Response-Header wie `X-Frame-Options`, `X-Content-Type-Options` und eine Content-Security-Policy.

**Behebung:**

Document Root auf `public/` stellen. In der Produktions-`.env`:

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
```

Danach `php artisan config:cache`. Wenn der Root nicht umgestellt werden kann, mindestens `.env` und `storage` per Webserver-Regel sperren. Das ist der schwächere Weg.

### M4. Laravel 11.33.2 ist außerhalb des Security-Supports

**Schweregrad:** mittel

**Datei:** `composer.lock`, Paket `laravel/framework`, Version `v11.33.2`

**Ausnutzung:**

Laravel 11 hat seit dem 12. März 2026 keine Security-Updates mehr. 11.33.2 liegt vor mehreren späteren 11.x-Fixes, unter anderem:

- CVE-2024-13918 und CVE-2024-13919, reflektiertes XSS, behoben in 11.36.0
- CVE-2025-27515, Umgehung von Datei-Validierung bei Wildcard-Regeln `files.*`, behoben in 11.44.1

Die Import-Regel in dieser App heißt `file`, nicht `files.*`. CVE-2025-27515 trifft diesen Aufruf so nicht. Ob die beiden XSS-Stellen im Framework auf einer Route dieser App erreichbar sind, ist **zu prüfen**; ein eigener Treffer im Anwendungscode wurde dazu nicht gefunden. Das Support-Ende bleibt: neu bekannt werdende Lücken in Laravel 11 werden nicht mehr geschlossen.

**Behebung:**

Auf eine unterstützte Laravel-Hauptversion gehen (zum Zeitpunkt dieses Reviews 12.x oder neuer) und die Anwendung danach gegen Anmeldung, API, Import und PDF laufen lassen. Ein reines Patch-Update innerhalb von 11.x schließt das Support-Ende nicht.

### N1. Personenbezogene Daten in Migrationen und Debug-Logs

**Schweregrad:** niedrig

**Dateien:**

- `database/migrations/0002_02_02_000005_create_members_table.php`, ab Zeile 27 (Klarnamen und E-Mail-Adressen, darunter eine reale Kontaktadresse und Testadressen)
- `app/Http/Controllers/Api/ApiGuestRegController.php`, Zeile 26 (komplettes Request-Array)
- `app/Http/Controllers/Api/ApiListController.php`, Zeilen 27–28 und 114–117
- `app/Jobs/SendSms.php`, Zeilen 89 und 102
- `routes/console.php`, Zeilen 8–25

**Ausnutzung:**

Die Migration liegt in jedem Klon. Wer das Repo liest, sieht Namen und E-Mail-Adressen, die nicht in ein öffentliches Repository gehören. Die Debug-Logs schreiben Anfragen, Namen und Mobilnummern nach `storage/logs`. Zusammen mit einem falschen Document Root (M3) wären die Logs abrufbar. Der Scheduler benennt das aktuelle Log um und löscht ältere Dateien, entfernt aber nicht, was schon geschrieben wurde. Ob die Adressen in der Migration noch aktiv sind, ist **zu prüfen**.

**Behebung:**

Seed-Daten auf offensichtlich künstliche Werte reduzieren. In Produktion `LOG_LEVEL=warning` und keine Request-Bodies, E-Mails oder Mobilnummern loggen. Die bereits veröffentlichten Adressen sind aus der Git-Historie nicht zuverlässig zu tilgen; die betroffenen Personen informieren, wenn das Repo öffentlich ist.

### N2. Cron-Token in der URL und unsicherer Vergleich

**Schweregrad:** niedrig

**Dateien:** `routes/web.php`, Zeilen 39–48; `config/app.php`, Zeile 126

**Ausnutzung:**

Die tokenlosen Routen aus K3 sind das eigentliche Problem. Bleiben nur die Varianten mit Token, steht das Geheimnis in der URL, der Vergleich ist nicht zeitkonstant, und ein leeres `CRON_SECRET` ist kein brauchbares Geheimnis. Allein daraus folgt kein Zugriff, solange K3 offen ist; nach dem Schließen von K3 wäre das der verbleibende Fehler der HTTP-Cron-Variante.

**Behebung:** siehe K3 (POST, Bearer, `hash_equals`, leeres Geheimnis ablehnen).

### N3. PhpSpreadsheet 1.29.8 und Dompdf 3.0.0

**Schweregrad:** niedrig

**Dateien:** `composer.lock`, `phpoffice/phpspreadsheet` `1.29.8`, `dompdf/dompdf` `v3.0.0`, aufgerufen über `maatwebsite/excel` `3.1.62` und `barryvdh/laravel-dompdf` `v3.1.1`. Eine veröffentlichte Dompdf-Config liegt im Repo nicht.

**Ausnutzung:**

CVE-2026-34084 betrifft `IOFactory::load`, wenn der Dateipfad selbst vom Client kommt (`phar://`, `ftp://`). Maatwebsite übergibt den temporären Pfad des Uploads, nicht den Originalnamen als Stream-Wrapper. Ein direkter RCE-Pfad über den Dateinamen wurde deshalb nicht angenommen. **Zu prüfen** bleibt, ob eine künftige Excel-Version oder ein eigener `load`-Aufruf den Client-Namen durchreicht. Andere Advisories derselben 1.29-Linie betreffen HTML-Export und Rechenzeit beim Parsen; der HTML-Export wird hier nicht verwendet, ein großes Arbeitsblatt kann den Import (K4) zusätzlich belasten.

Dompdf bekommt über H2 HTML aus Namen. `isRemoteEnabled` ist im Paket-Standard aus. Solange auf dem Server keine Config mit Remote-Zugriff veröffentlicht wurde (**zu prüfen**), zieht das PDF keine fremden URLs. JavaScript aus den Namen läuft im PDF-Viewer nicht wie im Backend-Browser; die relevante XSS-Stelle ist H2.

**Behebung:**

`phpoffice/phpspreadsheet` auf mindestens 1.30.7 anheben, soweit `maatwebsite/excel` 3.1 das erlaubt (`^1.29.7` lässt 1.30.x zu). Dompdf auf den aktuellen 3.x-Stand. Remote-Laden nicht einschalten.

## Priorisierte To-dos

1. **Sofort, vor weiterer öffentlicher Nutzung:** `GET /cron/schedule`, `GET /cron/queue` und `GET /api/cron` abschalten oder auf CLI umstellen (K3). Den SMSAPI-Token rotieren (H3).
2. **Registrierung schließen** und unbekannte Zeilen in `users` entfernen. Rechteprüfung an Backend und Import hängen (K2, K4).
3. **API absichern:** signiertes, kurzlebiges Token von rlweb, Allowlist für `host`, keine Identitätsübernahme über E-Mail oder Name, Gruppen serverseitig prüfen, `GET /api/details` ohne Schreibzugriff (K1, M1). Dieselbe Prüfung in rlweb nachziehen.
4. **Blade-Vorlagen entwaffnen** und vorhandene Texte in `email_templates` auf `@php` und `{!!` prüfen (K5).
5. **PDFs nur noch signiert und kurzlebig** ausliefern (H1). Namen im Backend und in der API escapen bzw. als Daten statt als HTML liefern (H2).
6. **Livewire auf mindestens 3.6.4** und Laravel auf eine unterstützte Hauptversion heben (H4, M4). PhpSpreadsheet mitziehen (N3).
7. **Webspace prüfen:** Document Root `public/`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `LOG_LEVEL=warning` (M3, N1).
8. **rlweb reviewen** entlang der Tabelle oben: `webid`, Formular-POST gegen die API, HTML-Ausgabe der Teilnehmer, Herkunft von `host`, gemeinsame Cookies.
