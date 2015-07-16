
QUIQQER Watcher
========

Das Paket stellt eine Art Protokollant zur Verfügung. Aktionen für bestimmte Gruppen / Nutzer können beobachtet und protokolliert werden.

Packetname:

    quiqqer/watcher


Features
--------

- Benutzer oder Gruppen Aktionen loggen
- Übersicht der ausgeführten Aktionen
- XML API, andere Module / Pakete können sich in den watcher mit einklinken

Installation
------------

Der Paketname ist: quiqqer/watcher


Mitwirken
----------

- Issue Tracker: https://dev.quiqqer.com/quiqqer/package-watcher/issues
- Source Code: https://dev.quiqqer.com/quiqqer/package-watcher


Support
-------

Falls Sie ein Fehler gefunden haben oder Verbesserungen wünschen,
Dann können Sie gerne an support@pcsg.de eine E-Mail schreiben.


License
-------


Entwickler
--------

Watch Events können über eine watch.xml eines Pakets registriert werden. Es können AJAX Funktionen und Events beobachtet werden.
Sie können somit, wenn das quiqqer/watch Modul installiert ist, über die watch.xml festlegen welche Funktionen / Events beobachtet werden können. 

Legen Sie hierzu eine watch.xml in Ihr Paket an.

**Beispiel:**

```xml
<quiqqer>
    <watch event="onSiteSave" exec="\QUI\Watcher\EventsReact::watchEventsText" />
    <watch ajax="package_quiqqer_tags_ajax_tag_edit" exec="\QUI\Tags\Watch::watchText" />
</quiqqer>
```


### `<watch>`

- event = Legt fest welches Event beobachtet wird
- ajax = Legt fest welche Ajax Funktion beobachtet wird

- exec
 `exec=""` wird für die Watch-Log Nachricht benötigt. Diese Methoden gibt ein String zurück welche in der Watch-Log erschein.  
 
 
 
```xml
<watch event="onSiteSave" exec="\QUI\Watcher\EventsReact::watchEventsText" />
```

*Immer wenn das Event onSiteSave ausgeführt wird, 
wird der Text von der Methode \QUI\Watcher\EventsReact::watchEventsText im Watch-Log hinzugefügt*

```xml
<watch ajax="package_quiqqer_tags_ajax_tag_edit" exec="\QUI\Tags\Watch::watchText" />
```

*Immer wenn die Ajax Funktion package_quiqqer_tags_ajax_tag_edit ausgeführt wird, 
wird der Text von der Methode \QUI\Tags\Watch::watchText im Watch-Log hinzugefügt*


Die Methoden welche in `exec=""` angegeben werden, bekommen auch einige Parameter.
 
 
**Beispiel event=""**

```php
<?php
/**
 * @param string $event - Name des events
 * @param array  $arguments - Event Parameter
 *
 * @return string
 */
static function watchEventsText($event, $arguments = array())
{


    return 'Watch-Log Aktion';
}
?>
```


**Beispiel ajax=""**

```php
<?php
/**
 * @param string $call  - Name der ajax Funktion
 * @param array $params - Parameter der Ajax Funktion
 * @param array $result - Ergebnis der Ajax Funktion
 *
 * @return string
 */
static function watchText($call, $params, $result)
{

    return 'Watch-Log Aktion';
}
?>
```
