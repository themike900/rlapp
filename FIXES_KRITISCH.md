# Die fünf kritischen Korrekturen

Diese Anleitung ist für den Betrieb, nicht für Sicherheitsexperten. Die Anwendung bleibt für die Mitglieder so bedienbar wie bisher. Geändert wurde nur, was ein Unbekannter ohne Konto ausnutzen konnte.

Zuerst lokal mit Laravel Herd prüfen, danach erst auf den Webspace. Nichts hier ist schon auf dem Server aktiv, solange dieser Branch nicht dort liegt.

Die Mitgliederseite rlweb (`rl_fahrtenliste.php` und `rl_details.php`) ruft rlapp weiter so auf wie heute. Dafür muss in der lokalen und in der Server-`.env` der neue Wert `RLAPP_API_TOKEN` **leer** bleiben, bis die spätere Umstellung von rlweb unten gemacht ist. Der Webspace-Cron braucht eine Umstellung, aber erst in der Reihenfolge aus der Checkliste. Die Token-Adressen gibt es auf dem heutigen Server schon.

## Zuerst in Herd

Herd legt für den Ordner `rlapp` normalerweise die Adresse `https://rlapp.test` an. Dieselbe Adresse steht in der lokalen rlweb. Wenn Herd eine andere Adresse anzeigt, diese nehmen und unten statt `rlapp.test` einsetzen.

Im Projektordner, in PhpStorm oder im Terminal:

```bash
git fetch origin
git checkout cursor/fix-critical-security-cec1
composer install
php artisan config:clear
```

In der lokalen `.env` ergänzen, die bestehende Datenbank und `APP_KEY` nicht anfassen:

```env
CRON_SECRET=lokales-testgeheimnis
RLAPP_API_TOKEN=
MAIL_MAILER=log
```

`MAIL_MAILER=log` sorgt dafür, dass ein Cron-Test keine echten Mails verschickt. `RLAPP_API_TOKEN` leer lassen, sonst antwortet die Mitgliederseite lokal mit „403“, bis rlweb das Token mitschickt.

Danach wieder `php artisan config:clear`.

Die automatischen Tests, die zu diesen Änderungen gehören:

```bash
php artisan test --filter='RegistrationTest|ImportAuthTest|CronAuthTest|SafeTemplateTest|ApiMemberLinkTest'
```

Erwartet: diese Tests sind grün. Eine Warnung `file_get_contents(.env)` erscheint nur, wenn gar keine `.env` da ist. Mit der Herd-`.env` entfällt sie.

Nicht auf `https://rlapp.royallouise.org` testen. Jeder `curl` unten geht an die lokale Herd-Adresse und schreibt in die lokale Datenbank.

## 1. Niemand kann sich selbst ein Backend-Konto anlegen

**Das Problem.** Unter `/register` konnte jede Person im Internet ein Konto anlegen und war sofort im Planungsbackend: Mitglieder, Handynummern, E-Mails, SMS, Fahrten.

**Was geändert wurde.** `routes/auth.php`. Die beiden Register-Routen sind weg. Dieselbe Adresse antwortet jetzt mit „Seite nicht gefunden“.

Vorher:

```php
Route::get('register', [RegisteredUserController::class, 'create'])
    ->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);
```

Nachher:

```php
Route::match(['get', 'post'], 'register', function () {
    abort(404);
});
```

**Warum der laufende Betrieb gleich bleibt.** Die 190 Mitglieder melden sich auf der WordPress-Seite an, nicht unter `/register`. Bestehende Backend-Benutzer melden sich weiter unter `/login` an. Es gibt auf der Login-Seite keinen Link „Registrieren“.

**Lokal prüfen.**

- Vorher, auf dem alten Stand: `https://rlapp.test/register` zeigt ein Formular „Konto anlegen“.
- Nachher, auf diesem Branch: dieselbe Adresse zeigt die Laravel-404-Seite. Es gibt kein Formular.
- `https://rlapp.test/login` zeigt weiterhin das Anmeldeformular. Mit dem bestehenden Benutzer kommt man ins Backend und sieht die Aktivitätenliste.

**Risiko.** Wer sich später ein zweites Backend-Konto wünscht, bekommt es nicht mehr über die Webseite. Konto dann von Hand anlegen, wie die bisherigen Konten auch. Neue Mitglieder der Webseite sind davon nicht betroffen.

## 2. Der Mitglieder-Import geht nur noch angemeldet

**Das Problem.** Die Import-Seite selbst war nur nach dem Login zu sehen. Die Annahmeadresse `POST /import` aber nicht. Wer die Adresse kannte, konnte eine Excel-Datei schicken und damit Gruppen und Handynummern bestehender Mitglieder überschreiben, ohne angemeldet zu sein.

**Was geändert wurde.** `routes/web.php`. Dieselbe Zeile, zusätzlich die Anmeldung:

```php
Route::post('/import', [ImportController::class, 'importMembers'])
    ->middleware(['auth'])
    ->name('import');
```

**Warum der laufende Betrieb gleich bleibt.** Der Import-Knopf sitzt im Backend, das man nur angemeldet sieht. Das Formular, das CSRF-Feld und die Excel-Datei sind unverändert. Es wird nicht zusätzlich eine bestätigte E-Mail verlangt, weil die bestehenden Konten sonst womöglich nicht mehr importieren könnten.

**Lokal prüfen.**

- Nicht angemeldet, oder in einem privaten Fenster: `https://rlapp.test/login` öffnen, dann im Terminal

  ```bash
  curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" -X POST https://rlapp.test/import
  ```

  Nachher: Status `302` und die Adresse endet auf `/login`. Es wird nichts importiert.
- Angemeldet im Backend: Menü „Mitglieder-Import“, eine gewohnte Excel-Datei importieren. Die Rückmeldung und die Mitgliederliste verhalten sich wie bisher.

**Risiko.** Keins für den normalen Import. Ein vergessener automatischer Aufruf von `/import` ohne Login gibt es im Code nicht.

## 3. Cron startet nur noch mit Geheimnis

**Das Problem.** `https://…/cron/schedule` und `https://…/cron/queue` haben den Aufgabenplaner und die Warteschlange ohne jedes Kennwort gestartet. Darüber gehen die Erinnerungsmails, die Fahrtenblätter und der Abendlauf, der Fahrten auf „durchgeführt“ setzt. `https://…/api/cron` hat ohne Kennwort Fahrten geschlossen, deren Datum in den nächsten fünf Tagen liegt. Beides konnte jeder aufrufen, auch nur durch das Laden eines Bildes auf einer fremden Seite.

**Was geändert wurde.** `routes/web.php` und `app/Http/Controllers/Api/ApiCronController.php`.

Vorher hat `/cron/schedule` ohne Token `schedule:run` ausgeführt. Nachher:

```php
Route::get('/cron/schedule', function () {
    abort(403);
});

Route::get('/cron/schedule/{token}', function (string $token) {
    $secret = (string) config('app.cron_secret');
    if ($secret === '' || !hash_equals($secret, $token)) {
        abort(403);
    }
    Artisan::call('schedule:run');
    return 'OK';
});
```

Dieselbe Sperre gilt für `/cron/queue`. `/api/cron` macht seine Arbeit nur noch bei `?token=` gleich `CRON_SECRET`.

Die Adressen mit Token gab es vorher schon. Neu ist: ohne Token passiert nichts, ein leeres Geheimnis gilt nicht, der Vergleich ist nicht mehr ein einfaches `!==`.

**Warum der laufende Betrieb gleich bleibt, wenn die Reihenfolge stimmt.** Der Webspace-Cron kann vor dem Deploy auf die Token-Adresse umgestellt werden. Der heutige Code akzeptiert diese Adresse bereits, sobald `CRON_SECRET` in der Server-`.env` steht. Die offene Adresse läuft parallel weiter, bis der neue Code liegt. Dann fällt sie weg, die Token-Adresse bleibt.

`/api/cron?token=…` ignoriert der heutige Code das `token` einfach und schließt die Fahrten weiter. Nach dem Deploy ist das Token Pflicht. Wer die URL schon vorher ergänzt, hat keine Lücke.

**Lokal prüfen.** `CRON_SECRET=lokales-testgeheimnis` und `php artisan config:clear`.

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://rlapp.test/cron/schedule
curl -s -w "\n%{http_code}\n" https://rlapp.test/cron/schedule/lokales-testgeheimnis
curl -s -o /dev/null -w "%{http_code}\n" https://rlapp.test/cron/queue
curl -s -w "\n%{http_code}\n" "https://rlapp.test/api/cron?token=lokales-testgeheimnis"
```

Nachher:

| Aufruf | Antwort |
| --- | --- |
| `/cron/schedule` | `403` |
| `/cron/queue` | `403` |
| `/cron/schedule/lokales-testgeheimnis` | Text `OK` |
| `/cron/schedule/falsch` | `403` |
| `/api/cron` | `403` |
| `/api/cron?token=lokales-testgeheimnis` | eine Zahl, wie viele lokale Fahrten gerade geschlossen wurden |

`OK` heißt: der Aufgabenplaner ist wirklich gelaufen. Mit `MAIL_MAILER=log` stehen Mails in `storage/logs`, sie gehen nicht raus. Liegt die Uhrzeit zwischen 10:00 und 15:05 oder um 22:00, und die lokale Datenbank ist eine Kopie der echten Fahrten, ändert dieser Aufruf lokale Datensätze. Das ist nur die Herd-Datenbank, nicht der Server. Wer das nicht will, die `OK`-Zeile weglassen. Die `403` ohne Token reicht als Beweis, dass die offene Adresse tot ist.

Vorher, auf dem alten Stand, lieferte `/cron/schedule` ohne Token ebenfalls `OK`.

**Risiko.** Wer den neuen Code auf den Server legt, bevor der Cron die Token-Adresse benutzt, bleiben die Erinnerungsmails aus, bis die Cron-URL nachgezogen ist. Die Reihenfolge steht in der Checkliste. Rückgängig: alter Code, oder die Token-URL ist schon umgestellt und bleibt gültig.

## 4. E-Mail- und SMS-Texte werden nicht mehr als Programm ausgeführt

**Das Problem.** Der Text aus „E-Mail-Textvorlagen“ wurde mit `Blade::render` gebaut. Blade führt PHP aus. Wer eine Vorlage speichern darf, konnte damit auf dem Server Befehle ausführen, sobald die nächste Mail oder SMS erzeugt wurde. Zusammen mit der offenen Registrierung und dem offenen Cron war das von außen erreichbar.

**Was geändert wurde.** Neue kleine Klasse `app/Support/SafeTemplate.php`. `app/Jobs/SendEmail.php` und `app/Jobs/SendSms.php` rufen sie auf statt `Blade::render`.

Vorher:

```php
$emailText = Blade::render($template->text, $data);
```

Nachher:

```php
$emailText = SafeTemplate::render($template->text, $data);
```

Eingesetzt werden nur noch einfache Platzhalter: `{{ $firstname }}`, `{{$firstname}}` und `{!! $firstname !!}`. HTML, das in der Vorlage selbst steht (`<p>`, `<br>`, `<b>`), bleibt. Der Wert des Platzhalters wird als Text eingesetzt, nicht als HTML. Bekannte Namen, die der Versand übergibt: `firstname`, `action_name`, `action_date`, `crew_start_at`, `action_start_at`, `captain`, `gst_name`, `sf_mobile`, `cancel_reason`, `action_id`, `preis`.

`@php`, `@if`, `{{ system('id') }}` und alles andere in geschweiften Klammern mit einer Funktion bleibt als sichtbarer Text stehen und läuft nicht.

**Warum der laufende Betrieb gleich bleibt.** Eine Vorlage der Form „Hallo {{ $firstname }}, am {{ $action_date }} ist {{ $action_name }}“ kommt genauso an wie bisher, einschließlich der HTML-Absätze drumherum. Anders wird es nur, wenn in einer Vorlage Blade-Steuerung steht.

**Lokal prüfen, bevor irgendetwas auf den Server geht.** Im Backend „Emails“ → „Email-Textvorlagen“. Jede Vorlage durchsehen.

- Erlaubt und unverändert wirksam: normaler Text, HTML-Tags, `{{ $firstname }}` und die anderen Namen von oben.
- Umschreiben, bevor dieser Branch auf den Server kommt: `@if`, `@php`, `@include`, `{{ $a ?? $b }}`, Funktionen in den Klammern. Sonst steht dieser Code wortwörtlich in der Mail oder SMS.

Gegenprobe im Terminal, die nichts versendet:

```bash
php artisan tinker --execute="echo App\Support\SafeTemplate::render('Hallo {{ \$firstname }} @php echo 123; @endphp', ['firstname' => 'Anna']);"
```

Erwartet: `Hallo Anna @php echo 123; @endphp`

Wer eine echte Mail sehen will: `MAIL_MAILER=log`, im Backend eine unwichtige Testfahrt an die eigene Adresse auslösen, dann `storage/logs/laravel.log` ansehen. Nicht den Test-SMS-Knopf benutzen, solange `SMSAPI_TOKEN` auf das echte Konto zeigt. Der Knopf ist unverändert und schickt eine echte SMS.

**Risiko.** Vorlagen mit `@if` ändern ihren Text, nicht das Programm. Deshalb der Blick in die Vorlagenliste vor dem Deploy. Die gespeicherten Vorlagen in der Datenbank werden von dieser Änderung nicht umgeschrieben.

## 5. Die Schnittstelle zur Mitgliederseite

**Das Problem.** rlweb schickt bei jedem Listenaufruf die `webid`, den Namen und die E-Mail. rlapp hat eine schon vergebene `webid` durch eine andere ersetzt, sobald die E-Mail passte. Wer die E-Mail eines Mitglieds kannte, konnte dessen Anmeldungen an eine fremde `webid` hängen. Zusätzlich hat `anm_opt=bereit_cr` jemanden als Crew eingetragen, auch ohne Crew-Gruppe. Ein Gast ließ sich an eine fremde Anmeldung hängen oder über seine Nummer löschen, ohne dass die Fahrt zusammenpasste.

Die Aufrufe selbst sind echt und sollen bleiben:

- `POST /api/list` macht rlweb serverseitig in `rl_fahrtenliste.php` (Listen).
- `GET /api/details/…` macht rlweb serverseitig in `rl_details.php`.
- `POST /api/rlreg` und `POST /api/gstreg` sind die Formulare im Browser des Mitglieds. Danach kommt die Weiterleitung zurück auf `https://{host}/intern/details`.

Ein Pflicht-Token ab dem ersten Deploy würde genau diese vier Aufrufe sofort mit 403 beenden. Die Listen wären leer, An- und Abmelden ginge nicht. Deshalb bleibt das Token aus, bis rlweb es sendet. Die drei konkreten Missbräuche sind trotzdem sofort zu.

**Was geändert wurde.**

`webid` in `app/Http/Controllers/Api/ApiListController.php`:

Vorher wurde die gespeicherte `webid` bei passender E-Mail oder passendem Namen immer überschrieben. Nachher nur noch, wenn sie leer ist oder mindestens 10000 ist. Die 10000er-Grenze ist die alte Verknüpfung, die der Code schon hatte: dabei wandern auch die Anmeldungen mit. Eine normale `webid` darunter bleibt stehen. Der erste Besuch eines importierten Mitglieds ohne `webid` setzt sie weiterhin.

Crew in `app/Http/Controllers/Api/ApiRegController.php`: `bereit_cr` nur mit Gruppe `cr`, oder `tr` bei den Fahrtarten `vf` und `gfx`. `bereit_sv` nur mit `sv`. Beides nur, wenn die Person in beiden Gruppen ist. Das ist dieselbe Regel, nach der die Webseite die Knöpfe anzeigt. `anm_tn` und `anm_wl` bleiben für jedes Mitglied. Eine unbekannte `anm_opt` trägt nichts ein und bricht nicht mehr mit einem Fehler ab. Die Weiterleitung zurück auf die Webseite bleibt.

Gäste in `app/Http/Controllers/Api/ApiGuestRegController.php`: anlegen nur, wenn `reg_id` zu derselben `action_id` gehört. Löschen nur, wenn der Gast zu dieser `action_id` gehört. Die Formulare auf der Webseite schicken genau diese Paare.

Optionales Schloss in `app/Http/Middleware/VerifyRlwebToken.php`, eingehängt nur an list, details, rlreg und gstreg. `RLAPP_API_TOKEN` leer: jeder bisherige Aufruf geht durch. Gesetzt: der Aufruf braucht den Header `X-RL-Token` oder das Formularfeld `rl_token`. Sonst 403.

**Warum der laufende Betrieb gleich bleibt.**

- Mitglied öffnet die Segelliste: rlweb sendet seine eigene `webid`. Die steht schon in der Datenbank, es wird nichts umgehängt, die Liste kommt wie bisher.
- Importiertes Mitglied ohne `webid` öffnet die Liste zum ersten Mal: die `webid` wird gesetzt, wie bisher.
- Crew-Knopf: den sieht nur, wer die Gruppe hat. Der Knopf trägt weiter ein.
- Teilnehmer-Knopf: unverändert.
- Gast anfragen und abmelden auf der eigenen Fahrt: unverändert.
- Nach dem Klick landet der Browser wieder auf `/intern/details` der Webseite. Das Feld `host` ist nicht angefasst.

**Lokal prüfen.** Einen Testmenschen in der lokalen Mitgliederliste nehmen, nicht auf dem Server. `webid` und E-Mail notieren. Die Person soll eine normale `webid` unter 10000 haben und nicht in der Gruppe `cr` sein. Eine offene Fahrt, ihre `id` aus der Aktivitätenliste oder aus der Adresse der Detailseite.

webid bleibt stehen:

```bash
curl -s -o /dev/null -w "%{http_code}\n" -X POST https://rlapp.test/api/list \
  -H "Content-Type: application/json" \
  -d '{"webid":99999,"firstname":"VORNAME","name":"NACHNAME","email":"DIE-ECHTE-EMAIL","list_type":"Segeltermine"}'
```

Nachher: Status `200` und eine JSON-Liste (oder `[]`). In der Mitgliederliste ist die `webid` noch die alte, nicht 99999. Vorher wäre sie auf 99999 gesprungen. Wenn doch etwas umhängt, die `webid` von Hand zurücksetzen. Auf dem alten Stand diesen curl nicht ausführen.

Crew ohne Gruppe, Fahrt-Id und webid einsetzen:

```bash
curl -s -D - -o /dev/null -X POST https://rlapp.test/api/rlreg \
  -d "webid=DIE-WEBID&actionid=DIE-FAHRT&host=rlweb.test&anm_opt=bereit_cr"
```

Nachher: Status `302`, `Location` zeigt auf `https://rlweb.test/intern/details?id=DIE-FAHRT&anm=true`. In der Crew-Planung dieser Fahrt taucht die Person nicht auf. Mit einer Person, die Gruppe `cr` hat, derselbe Aufruf: sie steht danach als Bereitschaft Crew da und kann im Backend wieder herausgenommen werden.

Teilnehmer, zur Kontrolle dass der normale Knopf-Weg nicht kaputt ist: `anm_opt=anm_tn` mit der eigenen Test-`webid`. Die Person steht danach bei den Teilnehmern und kann dort wieder gelöscht werden.

Token, erst ganz zum Schluss und danach wieder leeren:

```env
RLAPP_API_TOKEN=lokales-api-token
```

`php artisan config:clear`. Derselbe Listen-curl wie oben, ohne Header: `403`. Mit Header `-H "X-RL-Token: lokales-api-token"`: wieder `200`. Danach die Zeile leeren und noch einmal `config:clear`, sonst ist die lokale rlweb tot.

**Risiko und was noch offen ist.** Bis `RLAPP_API_TOKEN` gesetzt und rlweb umgestellt ist, kann jemand, der eine `webid` kennt oder durchprobiert, weiter die Liste abrufen, sich als Teilnehmer anmelden und eine Anmeldung dieser `webid` löschen. Das ist der Rest der offenen API. Zu ist: eine bestehende `webid` über die E-Mail stehlen, sich ohne Gruppe als Crew eintragen, einen Gast an eine andere Fahrt hängen. `webid` ab 10000 wird weiterhin einmalig auf die neue Nummer gezogen. Das ist die alte Migration. Wer sicher ist, dass niemand mehr so eine Nummer hat, ist damit fertig.

Das Token im Formular ist für eingeloggte Webseiten-Nutzer im Seitenquelltext sichtbar. Es hält Leute ohne Zugang zur Webseite ab. Ein Mitglied könnte es herauskopieren. Der Listen- und Detailaufruf bleibt auf dem Server und zeigt das Token nicht an.

Die PDF-Links und die Weiterleitung über `host` sind in diesem Branch nicht geändert.

## Deployment-Checkliste

Alles unter „Zuerst in Herd“ ist vorher durch. Die Vorlagen sind durchgesehen.

1. Auf dem Server in der `.env` ein langes `CRON_SECRET` eintragen. Noch nicht deployen. `RLAPP_API_TOKEN` leer lassen.
2. Den Webspace-Cron von  
   `https://rlapp.royallouise.org/cron/schedule`  
   auf  
   `https://rlapp.royallouise.org/cron/schedule/DAS-GEHEIMNIS`  
   umstellen. Entsprechend `/cron/queue/DAS-GEHEIMNIS`, falls die Queue über HTTP läuft. Die Adresse im Browser oder mit curl öffnen. Der heutige Code antwortet bereits `OK`.
3. Falls irgendwo `https://rlapp.royallouise.org/api/cron` aufgerufen wird: an die URL `?token=DAS-GEHEIMNIS` anhängen. Der heutige Code ignoriert das und arbeitet weiter.
4. Wenn der Server `php artisan config:cache` benutzt: nach der Änderung an der `.env` den Cache neu bauen. Sonst reicht, dass die `.env` beim nächsten Request gelesen wird.
5. Diesen Branch auf den Webspace bringen. `composer install` ist nicht nötig, es gibt keine neuen Pakete. Danach einmal `php artisan config:clear` oder den Config-Cache neu bauen.
6. Prüfen: `/cron/schedule` ohne Token ist `403`. Die Token-Adresse ist `OK`. Login, eine Aktivitätenliste, ein Import und eine Anmeldung über die Webseite einmal durchklicken.
7. Erst wenn alles das tut: rlweb umstellen, **danach** `RLAPP_API_TOKEN` setzen. Nicht umgekehrt.

rlweb, erst wenn Schritt 6 sitzt. Das Token nicht ins Git von rlweb schreiben. In `wp-config.php`, die nicht im Repo liegt:

```php
define('RLAPP_API_TOKEN', 'ein-langes-anderes-geheimnis');
```

In `rl_fahrtenliste.php` im Curl-Header-Array, und in `rl_details.php` im Curl-Header der Detailabfrage:

```php
'X-RL-Token: ' . (defined('RLAPP_API_TOKEN') ? RLAPP_API_TOKEN : ''),
```

In jedem Formular, das an `/api/rlreg` oder `/api/gstreg` geht, eine Zeile:

```php
<input type="hidden" name="rl_token" value="<?php echo defined('RLAPP_API_TOKEN') ? RLAPP_API_TOKEN : '' ?>">
```

Diese rlweb-Änderung zuerst veröffentlichen. Der alte rlapp ignoriert Header und Zusatzfeld. Dann in der rlapp-`.env` denselben Wert als `RLAPP_API_TOKEN` setzen und den Config-Cache erneuern. Liste, Detailseite, Anmelden, Abmelden, Gast anfragen einmal mit einem echten Testmitglied klicken.

## Rückgängig

- Code: den vorherigen Stand wieder auf den Server legen. Die Token-Cron-Adressen funktionieren auch mit dem alten Code, solange `CRON_SECRET` stehen bleibt. Die offenen Cron-Adressen wären dann wieder aktiv.
- `RLAPP_API_TOKEN` leeren und Config-Cache neu bauen, wenn die Webseite 403 zeigt. rlweb darf das Token weiter schicken, der alte wie der neue Code ohne Token kommen damit zurecht.
- Eine versehentlich geänderte `webid` in der Mitgliederliste wieder auf den notierten Wert setzen. Mit diesem Branch passiert das bei Nummern unter 10000 nicht mehr.

## Was geprüft wurde, und was nicht

Hier, ohne Herd und ohne die echte Datenbank: `php artisan about` startet Laravel 11.33.2. Die neuen Tests sind grün, 19 Tests, 48 Zusicherungen: Registrierung 404, Import ohne Login geht zur Anmeldung, Cron ohne Token 403, Cron mit Token läuft, `/api/cron` schließt eine Testfahrt nur mit Token, Platzhalter ersetzen und führen kein PHP aus, `webid` unter 10000 bleibt, leere `webid` und Nummern ab 10000 werden weiter verknüpft, Crew ohne Gruppe wird nicht eingetragen, Crew mit Gruppe und Teilnehmer werden eingetragen, Gäste nur an der passenden Fahrt, Token sperrt nur wenn es gesetzt ist.

Nicht geprüft: Klickpfad in Herd, der echte MySQL-Bestand, der Webspace-Cron, ob in den gespeicherten Vorlagen `@if` steht, ob noch irgendwo `webid` ab 10000 vorkommt. Der alte Beispieltest `GET /` erwartet Status 200 und bekommt 302, weil die Startseite auf die Aktivitätenliste und von dort zur Anmeldung weiterleitet. Das war vorher schon so und gehört nicht zu diesen Änderungen.
