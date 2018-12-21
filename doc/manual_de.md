IntegerNet_Solr
===============
Benutzer-/Entwickler-Handbuch

Allgemein
-----
IntegerNet_Solr ist ein Magento 2.x-Modul, das mit Hilfe von Apache Solr als Engine eine deutlich verbesserte Suche in Magento-Shops bietet.
Die Kernfunktionen sind ein Suchvorschaufenster mit Produkt- und Suchwortvorschlägen sowie bessere Suchergebnisse in punkto Qualität und Geschwindigkeit.

Features
--------
#### Allgemein
- Rechtschreibkorrektur, unscharfe Suche
- Zeigt zuerst exakte Suchergebnisse an und anschließend Suchergebnisse zu ähnlichen Suchbegriffen
- Vollständige Unterstützung der Multistore-Funktionalität von Magento
- Nutzung eines einzigen Solr-Kerns für mehrere Magento-Storeviews oder mehrerer Kerne
- Nutzung eines separaten Solr-Kerns nur für die Indizierung mit anschließendem Tausch der Kerne "on the fly" ("Swap")
- Erlaubt Logging aller Solr-Requests
- Prüft Erreichbarkeit und Konfiguration des Solr-Servers

#### Suchvorschlagsfenster
- Erscheint nach dem Eintippen der ersten zwei Buchstaben in das Suchfeld
- Zeigt Produktvorschläge, Kategorievorschläge, Attributvorschläge und Suchwortvorschläge
- Anzahl der Suchvorschläge jedes Typs ist in der Magento-Konfiguration einstellbar
- Anzuzeigende Attribute können in der Konfiguration definiert werden
- Für eine schnellere Suchvorschau werden die Requests an Magento vorbeigeleitet

#### Suchergebnisse
- Unterstützt alle Magento-Standardfunktionen wie Sortierung, Paginierung und Filter
- Erlaubt Mehrfachauswahl von Filtern
- Verwendet Suchergebnisse aus Solr für bessere Performance und Qualität
- Rendert Produkt-HTML-Blöcke bereits bei der Indizierung für schnellere Darstellung (optional)
- Ermöglicht die Konfiguration der Preisfilter-Schritte
- Der Solr-Index wird beim Erstellen/Bearbeiten/Löschen von Produkten automatisch aktualisiert

#### Modifizierung der Suchergebnisse
- Anpassung der Unschärfe ("Fuzziness")
- Erlaubt das Boosten bestimmter Produkte und Attribute
- Ermöglicht das Ausschließen einzelner Kategorien und Produkte von den Suchergebnissen
- Weiterleitung zur passenden Produkt- oder Kategorieseite bei exakter Übereinstimmung mit dem Suchbegriff
- Stellt Events zur Modifizierung des Indizierungsprozesses und der Suchergebnisse bereit

#### Kategorieseiten
- Der Solr-Index kann für die Darstellung der Produkte auf Kategorieseiten und für die Filternavigation genutzt werden

Systemvoraussetzungen
------------
- **Magento Community Edition** 2.1.x und 2.2.x
- **Solr** 4.x bis 6.x
- **PHP** 5.6 bis 7.0

Installation
------------
<!-- @TODO Copy and translate from OR link to installation.md -->

<a name="technischer-ablauf">Technischer Ablauf</a>
------------------

### Indizierung
Für jede Kombination aus Produkt und StoreView wird ein Solr-Dokument auf dem Solr-Server erzeugt. Dies erfolgt durch den Indizierungs-Mechanismus von Magento, der es erlaubt, auf jede Änderung am Produkt zu reagieren. Entweder können Sie eine komplette Neuindizierung vornehmen, die alle Produkte effizient (in Blöcken von je 1.000 Produkten, konfigurierbar) bearbeitet, oder eine laufende partielle Neuindizierung. Die partielle Neuindizierung wird ausgeführt, wenn ein beliebiges Produkt erstellt, geändert oder gelöscht wird und erneuert das entsprechende Dokument im Solr-Server. Dies passiert nur für die betroffenen Produkte, so dass der Solr-Index immer aktuell ist.

Die in Solr gespeicherten Daten beinhalten die folgenden Informationen:

- Produkt-ID
- Store-ID
- Kategorie-IDs
- Inhalt aller Produktattribute, die in Magento als "durchsuchbar" markiert sind
- Generiertes HTML für das Suchvorschau-Fenster, das die anzuzeigenden Daten und das Layout beinhaltet (z. B. Name, Preis, Produktbild, ...)
- Falls konfiguriert: Generiertes HTML für die Suchergebnisseite, einmal für den Gitter-Modus (Grid) und einmal für den Listen-Modus (List)
- IDs aller Optionen der filterbaren Attribute für die Filternavigation

Wenn Sie regelmäßig eine komplette Neuindizierung vornehmen, empfehlen wir Ihnen die **Swap**-Funktionalität.
Sie können das Modul so konfigurieren, dass es einen unterschiedlichen Solr-Kern zur Indizierung nutzt und dass anschließend die Kerne getauscht werden (`Shops -> Einstellungen -> Konfiguration -> Services -> IntegerNet_Solr -> Indizierung -> Cores tauschen nach vollständiger Neuindizierung`).

### Suchvorschläge
Wenn Sie die Suchvorschlags-Funktionalität nutzen, gibt es jedes Mal, wenn ein Kunde Zeichen ins Suchfeld im Frontend eingetippt hat, einen AJAX-Aufruf. Die Antwort davon beinhaltet den HTML-Code des Suchvorschau-Fensters, welches Produktdaten, Suchwortvorschläge, passende Kategorien und/oder Attribute anzeigt. 

Dabei wird eine PHP-Datei mit dem Namen `autosuggest.php` im Magento-Hauptverzeichnis per AJAX direkt aufgerufen. Ein Großteil der Magento-Funktionalität wird dabei nicht verwendet, wodurch sie in den meisten Umgebungen deutlich schneller ist. Da dabei keine Datenbank-Abfragen ausgeführt werden, müssen alle Daten, die für das Suchvorschaufenster benötigt werden, entweder direkt vom Solr-Server oder aus einer Textdatei kommen. Das Modul generiert automatisch Textdateien, die die Informationen enthalten, die von der Suchvorschaufunktion benötigt werden:

- Die Solr-Konfiguration (z. B. Zugangsdaten)
- Ein paar zusätzliche Konfigurationswerte
- Alle Kategoriedaten (Namen, IDs und URLs)
- Alle Attributdaten, die in der Konfiguration eingestellt sind (Optionsnamen, IDs und URLs)
- Einige Zusatzinformationen wie die Base-URL oder der Dateiname der Templatedatei (s. u.)
- Eine Kopie der Datei `src/view/frontend/templates/autosuggest/index.phtml`, die in Ihrem Theme verwendet wird. Alle Übersetzungstexte sind darin bereits in die korrekte Sprache übersetzt.

Die Informationen werden im Verzeichnis `var/cache/integernet_solr/` gespeichert. Diese Dateien werden automatisch in einem der folgenden Fälle neu erzeugt:

- AJAX-Aufruf im Frontend, während die Dateien in  `var/cache/integernet_solr/` nicht existieren.
- Die Konfiguration des Solr-Moduls wird gespeichert
- Der Button "Solr Suchvorschlagscache neu aufbauen" auf der Magento-Backend-Seite "Cache-Verwaltung" wird betätigt.

Wenn Sie also die gespeicherten Informationen erneuern lassen wollen, lösen Sie einen der drei obigen Fälle aus.

Beachten Sie, dass Sie nicht alle Magento-Funktionen zur Verfügung haben werden, wenn Sie das Template anpassen.
Versuchen Sie, sich an die Methoden zu halten, die in `src/view/frontend/templates/autosuggest/index.phtml` verwendet werden. Beispielsweise können Sie keine statischen Blöcke oder andere externen Informationen ohne zusätzliche Erweiterung verwenden.

Konfiguration
-------------

Die Konfiguration befindet sich im Administrationsbereich von Magento unter *Stores -> Konfiguration -> Solr*:

![Konfigurationsmenü](http://integernet-solr.com/download/documentation/Backend%20Config%20Menu%20M2%20DE.png)

Im Folgenden werden die Konfigurationsoptionen aufgelistet und beschrieben.

### Allgemein

![Allgemein](http://integernet-solr.com/download/documentation/General%20DE.png)

Im oberen Bereich werden Erfolgs-, Fehler-, Warn- und Informationsmeldungen ausgegeben. So wird automatisch geprüft, ob das Modul aktiviert ist, Zugangsdaten zum Solr-Server eingetragen sind und ob diese auch korrekt funktionieren.

#### Ist aktiv

Wenn dieser Schalter auf "Nein" steht, wird das Suchmodul im Frontend nicht genutzt werden. Stattdessen greift dann die Standardsuche von Magento. Sie haben die Möglichkeit, diesen Schalter für einzelne Websites oder StoreViews zu setzen.

<!-- #### Lizenzschlüssel

Damit das Modul korrekt funktioniert, benötigen Sie einen funktionierenden Lizenzschlüssel. Sie erhalten diesen nach Kauf und Bezahlung des Moduls von uns. Kontaktieren Sie uns unter solr@integer-net.de, wenn Sie Probleme mit Ihrem Lizenzschlüssel haben sollten.

Sie können das Modul zwei Wochen auch ohne Lizenzschlüssel testen. Erst anschließend ist der Lizenzschlüssel für das Funktionieren des Moduls notwendig.

Ein Lizenzschlüssel gilt jeweils für eine Live-Instanz und beliebig viele zugehörige Entwicklungs-, Test- und Staging-Systeme.

Achtung: Es wird keine Verbindung zu einem Lizenzserver o.ä. aufgebaut. Sobald der Lizenzschlüssel eingetragen ist, funktioniert das Modul autark.-->

#### Logging aktivieren

Wenn dieser Schalter aktiv ist, werden alle Anfragen zum Solr-Server gespeichert. Das betrifft sowohl die Suchvorschau als auch die eigentlichen Suchergebnisse. Sie finden die Logs anschließend im Verzeichnis `/var/log/` mit den Dateinamen `solr.log` bzw. `solr_suggestions.log`.

Die Logdateien werden ausschließlich zur Fehlersuche bzw. zur Optimierung der Suchergebnisse genutzt. Da die Datenmengen bei einer häufig genutzten Suchfunktion erheblich sein können, empfehlen wir, das Logging auf Produktivsystemen üblicherweise zu deaktivieren.

<a name="solr-server-data"></a>

### Server

![Server](http://integernet-solr.com/download/documentation/Solr%20Server%20DE.png)

In diesem Bereich werden die Zugangsdaten zum Solr-Server eingetragen. Wenn die Daten korrekt sind, erscheinen im oberen Bereich der Konfigurationsseite entsprechende Erfolgsmeldungen, andernfalls Fehlermeldungen.
Sollten Sie die Zugangsdaten nicht kennen, erhalten Sie diese von Ihrem Administrator bzw. Hoster, der den Solr-Server eingerichtet hat.

Wenn Sie Zugang zum Admin-Bereich des Solr-Servers haben, können Sie die Zugangsdaten wie folgt selbst herausfinden:

1. Wählen Sie links unten im Core-Selector den zu verwendenden Core aus:
 ![Solr-Admin 1](http://www.integer-net.de/download/solr/solr-admin-1.png)
2. Wählen Sie unterhalb des Core-Selectors "Query"
 ![Solr-Admin 2](http://www.integer-net.de/download/solr/solr-admin-2.png)
3. Klicken Sie "Execute Query"
 ![Solr-Admin 3](http://www.integer-net.de/download/solr/solr-admin-3.png)
4. Im oberen Bereich auf der rechten Seite sehen Sie jetzt die für Ihren Beispiel-Request verwendete URL:
 ![Solr-Admin 4](http://www.integer-net.de/download/solr/solr-admin-4.png)

Die URL wird wie folgt in die einzelnen Teile aufgeteilt:

![Solr-Admin-URL](http://www.integer-net.de/download/solr/solr-config-server.png)

Die einzelnen Teile werden dann wie folgt in die Konfiguration eingetragen:

![Solr Serverkonfiguration](http://integernet-solr.com/download/documentation/Solr%20Server%20URL%20DE.png)

Achten Sie darauf, dass das Feld *Kern* keine Schrägstriche enthält, das Feld *Path* aber mindestens je einen Schrägstrich am Anfang und am Ende.

#### HTTP-Übertragungsmethode

Bleiben Sie hier bei der Standardmethode *cURL*, wenn Sie keine Fehlermeldung erhalten. Andernfalls können Sie auf die Methode *file_get_contents* wechseln. Die Verfügbarkeit der Methoden hängt von den Server-Einstellungen des Magento-Servers ab.

#### Use HTTPS

Wenn Sie in Ihrem Shop alle Seiten per https laden, sollten Sie dies auch bei der Suche beibehalten. Andernfalls kann es zu einer Warnung des Browsers kommen, dass vermeintlich unsichere Elemente geladen werden. 

#### Use HTTP Basic Authentication

Sie können Zugriffe auf Ihren Solr-Server per HTTP-Authentifizierung kontrollieren. Falls dies der Fall ist, aktivieren Sie bitte diese Funktion in IntegerNet_Solr und tragen die zugehörigen Anmeldedaten in den folgenden Feldern ein.

#### HTTP Basis-Authentifizierung: Benutzername

Tragen Sie hier den Benutzernamen ein, wenn dieser für den Zugriff von Magento auf den Solr-Server notwendig sein sollten.

#### HTTP Basis-Authentifizierung: Passwort

Tragen Sie hier das Passwort ein, wenn dieses für den Zugriff von Magento auf den Solr-Server notwendig sein sollten.

<!-- ### Erreichbarkeitsprüfung

![Erreichbarkeitsprüfung](http://www.integer-net.de/download/solr/integernet-solr-config-connection-check-de.png)

Um sicherzustellen, dass der Solr-Server nicht unbemerkt ausfällt, kann das Modul automatisch in regelmäßigen Abständen die Verbindung überprüfen.

#### Erreichbarkeit des Solr-Servers automatisch prüfen

Wird der Wert "Ja" gewählt, erfolgt die automatische Erreichbarkeitsprüfung alle 5 Minuten.

#### E-Mail-Benachrichtigung nach der X-ten fehlgeschlagenen Prüfung hintereinander senden

Wollen Sie bei jeder fehlgeschlagenen Verbindungsprüfung benachrichtigt werden, tragen Sie den Wert 1 ein.

#### E-Mail-Empfänger

Die Benachrichtigungen über die Erreichbarkeitsprüfung werden per E-Mail an die hier eingetragenen Adressen verschickt.

#### E-Mail-Vorlage

Sie haben die Möglichkeit, eine eigene E-Mail-Vorlage für die Erreichbarkeitsprüfung anzulegen. Diese wird im Magento Backend in `Marketing -> Kommunikation -> E-Mail-Vorlagen`  hinterlegt.
Falls Sie eine eigene Vorlage angelegt haben, stellen Sie bitte sicher, dass die angelegte E-Mail-Vorlage mit der ausgewählten Vorlage in der Konfiguration des Solr-Moduls übereinstimmt.

#### E-Mail-Absender

Für den Versand der Benachrichtigung können Sie den E-Mail-Absender auswählen.-->

### Indizierung

![Indizierung](http://integernet-solr.com/download/documentation/Indexing%20DE.png)

#### Anzahl Produkte pro Durchlauf

Die hier eingetragene Anzahl Produkte wird bei der Indizierung (s. o.) gleichzeitig verarbeitet, entsprechend viele Produktdaten werden in einen einzigen Request zum Solr-Server aufgenommen. Von dieser Einstellung ist die Performance der Indizierung stark abhängig. Reduzieren Sie den Wert testweise, falls Sie Fehler bei der Indizierung erhalten.

#### Alle Solr-Indexeinträge vor Neuindizierung löschen

Diese Einstellung sollten Sie nur deaktivieren, wenn Sie nächtlich den Index komplett neu aufbauen, aber keinen SWAP-Kern (s.u.) nutzen.
Wenn diese Einstellung aktiv ist, wird der Solr-Index zu Beginn einer vollständigen Neuindizierung komplett geleert und anschließend neu erstellt.

#### Cores tauschen nach vollständiger Neuindizierung

Wenn Sie regelmäßig den Index neu aufbauen (z. B. nächtlich), ist es sinnvoll, die Funktion zum Tauschen der Kerne einzusetzen und einen zweiten Kern zu verwenden. Aktivieren Sie in dem Fall diese Option und tragen Sie im Feld *Name des Cores, mit dem der aktive Core getauscht werden soll* den Namen des zweiten Kerns ein.

### Unscharfe Suche

![Unscharfe Suche](http://integernet-solr.com/download/documentation/Fuzzy%20Search%20DE.png)

#### Ist aktiv für Suche

Wenn diese Einstellung ausgeschaltet ist, werden nur exakte Suchtreffer registriert. Eine Fehlerkorrektur findet dann nicht mehr statt. Dafür ist die Suche schneller, wenn diese Einstellung deaktiviert ist.

#### Sensibilität für Suche

Hier können Sie eintragen, wie empfindlich die unscharfe Suche sein soll. Der Wert muss zwischen 0 und 1 liegen, mit dem Punkt (.) als Dezimaltrennzeichen, also z. B. *0.75*.
Je niedriger der Wert, desto mehr Treffer werden Sie erhalten, da Schreibfehler großzügiger korrigiert werden und z. B. für die Eingabe "rot" auch der Wert "rosa" akzeptiert wird, der von den Buchstaben her relativ ähnlich ist.
Testen Sie hier einen möglichst guten Wert für Ihren Shop aus.
Wir empfehlen Werte zwischen 0.6 und 0.9.

#### Anzahl ausreichender direkter Suchergebnisse

Die direkten Suchergebnisse werden bei aktivierter unscharfen Suche automatisch um unscharfe Suchergebnisse ergänzt.
Sie können diese Funktion einschränken, indem Sie die Anzahl ausreichender direkter Suchergebnisse festlegen. Werden mindestens so viele direkte Suchergebnisse gefunden, wird keine unscharfe Suche durchgeführt. Wird der Wert 0 oder kein Wert eingegeben, dann wird die unscharfe Suche immer ausgeführt.

#### Ist aktiv für Suchvorschläge

Wie oben, aber für die Suchvorschlags-Box (Autosuggest) individuell einstellbar. Es kann interessant sein, diese Funktion nur für die Suchvorschläge aus Performancegründen auszuschalten.

#### Sensibilität für Suchvorschläge

Wie oben, aber für die Suchvorschlags-Box (Autosuggest) individuell einstellbar.

#### Anzahl ausreichender direkter Suchergebnisse für Suchvorschläge

Analog zur Suche haben Sie auch bei den Suchvorschlägen die Möglichkeit, die unscharfe Suche nicht durchführen zu lassen, wenn bereits ausreichend direkte Treffer für Suchvorschläge vorhanden sind.
Werden mindestens so viele direkte Suchergebnisse gefunden wie eingetragen, wird keine unscharfe Suche durchgeführt.
Wird der Wert 0 oder kein Wert eingegeben, dann wird die unscharfe Suche für Suchvorschläge immer ausgeführt.

### Suchergebnisse

![Suchergebnisse](http://integernet-solr.com/download/documentation/Search%20Results%20DE.png)

#### Such-Operator

Hier haben Sie die Wahl zwischen *UND* und *ODER*. Der Such-Operator wird eingesetzt, wenn es mehr als einen Suchbegriff in der Anfrage gibt, z. B. "rotes Shirt". Bei *UND* werden nur Ergebnisse ausgegeben, die auf beide (bzw. alle) Suchbegriffe passen, bei *ODER* werden dafür auch Ergebnisse ausgegeben, die nur auf einen der Suchbegriffe passen.
In den meisten Fällen ist *UND* die bessere Einstellung.

<!-- #### Position der Filter

Filter können entweder in der linken Spalte neben den Produkten oder oberhalb der Produkte angezeigt werden. Letztere ist empfehlenswert bei einem eher schmalen Template.

#### Maximalanzahl Filteroptionen pro Filter

Gibt es für Filter sehr viele Filteroptionen, kann aus Gründen der Übersichtlichkeit die Zahl der angezeigten Filteroptionen eingeschränkt werden. Wird hier der Wert "0" eingetragen, werden alle Filteroptionen angezeigt.

#### Filteroptionen alphabetisch sortieren

Normalerweise werden die Filteroptionen nach der Anzahl der Treffer sortiert. In einigen Fällen ist es sinnvoll, sie stattdessen alphabetisch zu sortieren. Die alphabetische Sortierung kann über dieses Feld aktiviert werden.-->

#### Sortierung von Filteroptionen

In Magento werden die Filterwerte standardmäßig anhand der Position in der Attributkonfiguration auch im Frontend in der Filternavigation angezeigt. Sie können alternativ alphabetisch oder anhand der Trefferanzahl absteigend sortiert werden.

Bitte beachten Sie, dass diese Konfiguration sowohl für die Filternavigation auf Suchergebnisseiten als auch auf Kategorieseiten gilt.

#### Solr-Priorität von Kategorienamen

Hier können Sie einstellen, mit welcher Priorität Kategorienamen im Solr-Index verarbeitet werden. Ein Beispiel: Wenn der Suchbegriff "schwarze Shirts" hauptsächlich solche Artikel im Suchergebnis anzeigen soll, die in der Kategorie "Shirts" enthalten sind, tragen Sie hier einen höheren Wert ein.
Der Standardwert ist 1. Wenn Sie einen höheren Wert eintragen, werden Kategorienamen im Solr-Index stärker beachtet.

#### Produkte anzeigen, die nicht auf Lager sind

Als Standardeinstellung werden auch Produkte, die nicht auf Lager sind, in den Suchergebnissen angezeigt. Um ausverkaufte Produkte auf den Suchergebnisseiten auszublenden, wählen Sie "Nein".

#### Solr-Prioritäts-Multiplikator für ausverkaufte Produkte

Dieser Faktor beeinflusst, wie sich der Lagerstatus des Produkts auf das Ranking in den Suchergebnissen auswirkt. Wenn Sie es bevorzugen, ausverkaufte Produkte in der Suchergebnisliste anzuzeigen, wählen Sie einen Wert, der größer als 0 ist. Um ausverkaufte Produkte ans Ende der Ergebnisliste zu setzen, geben Sie "0.1" ein. Der Wert "1" bedeutet, dass der Lagerstatus keinen Einfluss auf das Suchergebnis-Ranking hat.

#### Größe der Preis-Schritte

Diese Einstellung ist für den Preisfilter wichtig. Hier kann man einstellen, in welchen Schritten die einzelnen Intervalle definiert sein sollen. So führt z. B. *10* zu den Intervallen *0,00-10,00*, *10,00-20,00*, *20,00-30,00* usw.

#### Obergrenze der Preis-Schritte

Auch diese Einstellung ist für die Steuerung des Preisfilters gedacht. Hierüber wird das oberste Intervall definiert. Beim Wert *200* wäre das also *ab 200,00*. In diesem Intervall werden alle Produkte zusammen gefasst, die mehr als 200,00 kosten.

#### Individuelle Preisintervalle verwenden

Wenn Sie keine lineare Einteilung der Intervalle wünschen und mindestens Solr 4.10 einsetzen, können Sie hier die gewünschten Intervallgrenzen für den Preisfilter individuell einstellen. Beim Beispiel *10,20,50,100,200,300,400,500* wären das die Schritte *0,00-10,00*, *10,00-20,00*, *20,00-50,00* usw. bis *400,00-500,00* und *ab 500,00*.

#### Weiterleitung zur Produktseite bei 100 % Übereinstimmung mit einem dieser Attribute

Wird als Suchbegriff ein Wert eingegeben, der mit einem wichtigen Attributwert eines Produkts exakt übereinstimmt, können Sie für diesen Fall eine direkte Weiterleitung zum Produkt aktivieren. Dadurch wird der Weg zum Produkt weiter verkürzt, indem das Anzeigen der Suchergebnisseite übersprungen wird.
Hier sollten nur Attribute genutzt werden, bei denen die Zuordnung zum Produkt eindeutig ist.

#### Weiterleitung zur Kategorieseite bei 100 % Übereinstimmung mit einem dieser Attribute

Wie bei der direkten Weiterleitung zu Produkten können Sie auch eine Weiterleitung zu exakt übereinstimmenden Kategorieseiten aktivieren.
Hier sollten nur Attribute genutzt werden, bei denen die Zuordnung zur Kategorie eindeutig ist.

### Kategorieseiten

![Kategorieseiten](http://integernet-solr.com/download/documentation/Category%20Pages%20DE.png)

#### Solr für die Darstellung von Produkten auf Kategorieseiten verwenden

Das Aktivieren dieser Funktion führt dazu, dass die Produkte auf Kategorieseiten von Solr dargestellt werden. Besonders in Online-Shops mit einer Vielzahl von Produkten oder filterbaren Attributen in der Filternavigation können so die Ladezeiten von Kategorieseiten deutlich verringert werden.
Wird diese Funktion aktiviert, ist danach eine Reindizierung des Solr Suchindex notwendig, bevor die Änderungen im Frontend des Shops übernommen werden.

#### Solr für die Indizierung von Kategorie-Seiten verwenden

Wenn diese Funktion aktiviert ist, werden Kategorien in den Suchvorschlägen angezeigt, deren Namen oder Beschreibungen zum Suchbegriff passen. Für eine feinere Einstellung der vorgeschlagenen Kategorien können Sie einzelne Kategorien vom Index ausschließen.

#### Kategorien als Suchergebnisse anzeigen

Hier können Sie entscheiden, ob Kategorien, die ein Treffer für den Suchbegriff sind, in den Suchergebnissen als Treffer angezeigt werden sollen. Sie werden in einem separaten Tab angezeigt: ein Tab für passende Produkte, ein Tab für passende Kategorien.

#### Maximale Anzahl der Suchergebnisse

Wenn ein Suchbegriff zu viele Kategorien als Suchergebnis angeibt, kann hier die Anzahl der anzuzeigenden Treffer bestimmt werden. Geben Sie dafür eine ganze Zahl ein.

#### Unscharfe Suche aktivieren

Wie die unscharfe Suche bei den Suchvorschlägen und der Produktsuche können Sie auch unscharfe Treffer für Kategorien zulassen. Wählen Sie dafür den Wert "Ja".

#### Sensibilität für Suche

Wenn die unscharfe Suche für Kategorieseiten aktiviert ist, können Sie in diesem Feld die Feinjustierung der unscharfen Suche vornehmen. Tragen Sie einen Wert zwischen 0 und 1 ein. Kleinere Werte, z.B. 0.5, führen zu mehr unscharfen Ergebnissen.

#### Produkte anzeigen, die nicht auf Lager sind

Als Standardeinstellung werden auch Produkte, die nicht auf Lager sind, in den Produktlisten der Kategorieseiten angezeigt. Um ausverkaufte Produkte auf den Kategorieseiten auszublenden, wählen Sie "Nein".

<!-- ### CMS

![CMS-Seiten](http://www.integer-net.com/download/solr/integernet-solr-config-CMS-de.png)

#### Solr für die Indizierung von CMS-Seiten verwenden

Um CMS-Seiten in den Suchvorschlägen anzuzeigen, muss diese Funktion aktiviert sein. Sie funktioniert ähnlich wie die oben genannte Indizierung der Kategorien. Für eine feinere Einstellung können auch hier einzelne CMS-Seiten vom Index ausgeschlossen werden.-->

### Suchvorschlags-Box

Die Suchvorschlags-Box wird auch als "Suchvorschau" oder "Autosuggest" bezeichnet.

![Suchvorschlags-Box](http://integernet-solr.com/download/documentation/Autosuggest%20Box%20DE.png)

#### Ist aktiv

Bei Deaktivieren dieser Einstellung wird kein Suchvorschaufenster angezeigt.

#### Maximale Anzahl Suchwort-Vorschläge

Die Anzahl der Suchwort-Vorschläge in der Suchvorschlags-Box. Abhängig von Ihren Produkten wird der eingegebene Suchbegriff um sinnvolle Varianten ergänzt. Bei Eingabe von "re" im Demo-Shop erscheinen z. B. die folgenden Vorschläge: *regular…*, *resistant…*, *refined…*, *red…*.

#### Maximale Anzahl Produkt-Vorschläge

Die Anzahl der in der Suchvorschau angezeigten Produkte.

#### Maximale Anzahl Kategorie-Vorschläge

Die Anzahl der in der Suchvorschau angezeigten Kategorien. Wenn die Funktion "Solr für die Indizierung von Kategorie-Seiten verwenden" ebenfalls aktiviert ist, werden jene Kategorien angezeigt, deren Namen und Beschreibungen zum Suchbegriff passen. Andernfalls werden die Kategorien aufgeführt, in denen zum Suchbegriff passende Produkte enthalten sind.

<!-- #### Maximale Anzahl CMS-Seiten-Vorschläge

Die Anzahl der in der Suchvorschau angezeigten CMS-Seiten. Diese Vorschläge können nur dann angezeigt werden, wenn für die Funktion "Solr für die Indizierung von CMS-Seiten verwenden" der Wert auf "Ja" gesetzt ist.-->

#### Unscharfe Suche ist aktiv für Kategorien

Sie können hier einstellen, ob Kategorievorschläge auch unscharfe Treffer anzeigen sollen. Bei sehr spezifischen Kategorienamen (z. B. mit Ziffern für Größen), kann es besser sein, die unscharfen Kategorievorschläge zu deaktivieren.

#### Sensibilität für Kategorien

Ist die unscharfe Suche für Kategorien in den Suchvorschlägen aktiviert, lässt sich hier die Sensibilität der unscharfen Ergebnisse anpassen. Je größer der Wert, desto exakter sind die Ergebnisse.

<!-- #### Kompletten Kategorie-Pfad anzeigen

Ist diese Einstellung aktiv, werden nicht nur die Kategorienamen angezeigt, sondern auch deren Elternkategorien als Pfad, beispielsweise "Electronics > Cameras > Accessories" statt nur "Accessoires".-->

#### Typ von Kategorie-Links

Hier geht es um den Link, der hinter den angezeigten Kategorien steht. Die Optionen sind:
- Suchergebnisseite mit gesetztem Kategoriefilter, sodass nur Produkte in der gewählten Kategorie angezeigt werden
- Kategorieseite

#### Attributfilter-Vorschläge

Hier können Sie beliebig viele Attribute eintragen, die in der Suchvorschau mit den am häufigsten vorkommenden Optionen dargestellt werden. Sie können jeweils das Attribut auswählen und die Anzahl der angezeigten Optionen definieren. Außerdem können Sie die Reihenfolge der Attribute bestimmen - das Attribut mit dem kleinsten Wert bei "Sortierung" wird zuoberst angezeigt.
Es stehen nur Attribute zur Auswahl, die die Eigenschaft "Filternavigation auf Suchergebnisseiten verwenden" haben.

#### Produkte anzeigen, die nicht auf Lager sind

Als Standardeinstellung werden ausschließlich Produkte, die auf Lager sind, in den Suchvorschlägen angezeigt. Um auch ausverkaufte Produkte in der Suchvorschlagsbox anzuzeigen, wählen Sie "Ja".

<!-- ### SEO

![SEO](http://www.integer-net.de/download/solr/integernet-solr-config-seo-de.png)

#### Die folgenden Seiten vor Bots schützen:

Wählen Sie aus, welche der von IntegerNet_Solr verarbeiteten Seiten für Bots und Suchmaschinen gesperrt werden sollen. Diese Seiten erhalten im Meta-Element Robots den Wert "NOINDEX,NOFOLLOW". Bitte beachten Sie, dass diese Konfiguration starke Auswirkungen auf das Suchmaschinen-Ranking Ihres Shops haben kann.-->

Modifikation der Reihenfolge der Suchergebnisse
-------------

Die Suchergebnisse werden bereits mit den Basiseinstellungen in eine Reihenfolge gebracht, die hauptsächlich von der Häufigkeit und der Position der Vorkommen der Suchbegriffe in den Produkteigenschaften abhängt. Erfahrungsgemäß werden damit bereits gute Ergebnisse erzielt - deutlich bessere als mit der Standardsuche von Magento.

Es gibt allerdings weitere Möglichkeiten der Anpassung:

### Boosting von Attributen

Wenn Suchbegriffe im Namen oder der Artikelnummer eines Artikels vorkommen, sollte dies höher gewertet werden als wenn der gleiche Suchbegriff nur im Beschreibungstext vorkommt. Daher werden bereits im Standard manche Attribute höher priorisiert als andere.

Die Priorisierung erfolgt anhand des Wertes "Solr-Priorität", die man jedem Produktattribut zuweisen kann.
<!-- Diese neue Eigenschaft kann man in der Auflistung der Attribute (unter *Katalog -> Attribute -> Attribute verwalten*) bereits sehen:

![Attribut-Tabelle](http://www.integer-net.de/download/solr/integernet-solr-attribute-grid-de.png)

Die Tabelle ist hier bereits nach dem neuen Wert "Solr-Priorität" sortiert.
Den Wert können Sie in den Attributeigenschaften auch setzen:-->

![Attribut-Ansicht](http://integernet-solr.com/download/documentation/Attribute%20boost%20DE.png)

Mit diesem Wert wird die errechnete Priorität des Suchbegriffes für das Produkt multipliziert, wenn der gesuchte Begriff in dem Attribut gefunden wird. Daher entspricht *1.0* dem Standard - hier findet keine Modifikation statt. Somit können Sie die Priorität von einzelnen Attributen erhöhen oder senken. Wir empfehlen Werte zwischen 0.5 und höchstens 10.

Beachten Sie, dass Sie nach der Anpassung der Suchpriorität den Solr-Index neu aufbauen müssen.

### Boosting von Produkten

Es kommt immer mal wieder vor, dass einzelne Produkte hervorgehoben werden sollen, sei es, weil sie die Topseller sind, sei es, weil sie abverkauft werden sollen. Hierfür gibt es die Möglichkeit, die Priorität einzelner Produkt hoch- oder herabzusetzen.

Dafür gibt es das neue Produktattribut "Solr-Priorität" im Tab "Solr" der Produktansicht im Backend.

![Produktansicht](http://integernet-solr.com/download/documentation/Product%20Details%20DE.png)

Hierüber haben Sie die Möglichkeit, ein Produkt, sofern es zu den Suchbegriffen passt, weiter oben oder weiter unten zu platzieren als seine Standard-Position. Wir empfehlen hier Werte zwischen 0.5 und höchstens 10. Der Mechanismus ist der gleiche wie beim Boosting von Attributen. Eine Neuindizierung ist nach der Anpassung nicht erforderlich, sofern die Index-Aktualisierung aktiviert ist.

<!-- ### Ausschließen von CMS-Seiten

Wenn gewünscht, können einzelne CMS-Seiten von den Suchergebnissen ausgeschlossen werden. Die Einstellungsmöglichkeiten dafür finden Sie im Magento-Backend in der jeweiligen CMS-Seite im Tab "Solr".

![CMS-Seiten-Ansicht](http://www.integer-net.de/download/solr/integernet-solr-CMS-exclude-de.png)

Setzen Sie im Feld "Diese Seite vom Solr-Index ausschließen" den Wert auf "Ja", wird die CMS-Seite nicht in den Suchergebnissen angezeigt.
Im Feld "Solr-Priorität" können Sie durch die Eingabe einer Zahl, die größer als 1 ist, dieser CMS-Seite ein höheres Gewicht in den Suchergebnissen geben.-->

Kategorieanpassungen
---------------------

![Kategorieansicht](http://integernet-solr.com/download/documentation/Category%20Details%20DE.png)

### Diese Kategorie vom Solr-Index ausnehmen
Bei Bedarf gibt es die Option, Kategorien von der Solr-Suche auszuschließen. Die Einstellungsmöglichkeiten dafür finden Sie im Magento-Backend in der jeweiligen Kategorie im Tab "Solr". Wird der Wert auf "Ja" gesetzt, erscheint diese Kategorie nicht mehr als Vorschlag in den Suchvorschlägen.

### Kind-Kategorien vom Solr-Index ausnehmen
Neben dem Ausschließen einer Kategorie aus dem Index können auch nur deren untergeordnete Kindkategorien aus der Suche ausgeschlossen werden. In den Suchvorschlägen werden die auf diese Art ausgeschlossenen Kategorien nicht mehr angeboten. Die Produkte dieser Kategorien werden jedoch weiterhin als Suchergebnisse angezeigt.

### Filter entfernen
Selbst wenn IntegerNet_Solr nicht zum Laden der Produkte auf Kategorieseiten genutzt wird, können Sie das Modul nutzen um unnötige Filter von bestimmten Kategorieseiten zu entfernen. Zum Beispiel können Sie so verhindern, dass der Filter "Geschlecht" auf der Kategorieseite für Herrenbekleidung angezeigt wird.

<!-- ### Position der Filter
Für jede Kategorie können Sie bestimmen, wo die Filter angezeigt werden, auch abweichend vom Standardwert, den Sie in der Konfiguration von IntegerNet_Solr hinterlegen. Filter können entweder in der linken Spalte neben den Produkten oder oberhalb der Produkte angezeigt werden.-->

### Solr-Priorität

Nutzen Sie das Feld “Solr-Priorität” um der Kategorie in den Suchergebnissen mehr Gewicht zu verleihen. Je höher der eingetragene Wert ist, desto höher ist auch der Boost-Faktor für diese Kategorie und damit ihr Ranking in den Suchergebnissen.


Template-Anpassungen
--------------------

Wenn Sie ein Nicht-Standard-Template verwenden, müssen voraussichtlich ein paar Anpassungen gemacht werden.
Das Template des Suchvorschaufensters und der Suchergebnisseite ist in `src/view/frontend/templates/autosuggest/` (PHTML-Dateien) definiert sowie in `src/view/frontend/web/autosuggest.css` für die CSS-Datei, die auf jeder Seite eingebunden wird.

### Suchvorschaufenster
Sie können die Dateien `src/view/frontend/templates/autosuggest/index.phtml` und `src/view/frontend/templates/autosuggest/item.phtml` bearbeiten, um das Erscheinungsbild des Suchvorschaufensters anzupassen.
Achtung: Da der generierte HTML-Code für jedes Produkt im Solr-Index gespeichert ist, müssen Sie nach Änderungen an der Datei `src/view/frontend/templates/autosuggest/item.phtml` eine Neuindizierung vornehmen.

Bitte beachten Sie: Sie können in Ihrer `src/view/frontend/templates/autosuggest/index.phtml` nicht alle Magento-Funktionen verwenden.
Versuchen Sie, sich an die in `src/view/frontend/templates/autosuggest/index.phtml` genutzten Funktionen zu halten. Da der HTML-Code für die einzelnen Produkte von Magento generiert wird, können Sie dort hingegen alle Magento-Funktionen verwenden.

Wenn Sie Produkt-, Kategorie-, Attribut- oder Suchwortvorschläge in der Suchvorschaufunktion nicht verwenden, schalten Sie sie bitte auch in der Konfiguration aus, um die Performance zu verbessern.

Events
---------------------
Zur Anpassung des Moduls sind verschiedene Events integriert, die von einem externen Modul per Observer aufgegriffen werden können. Folgende Events sind in IntegerNet_Solr enthalten:

- integernet_solr_get_product_data
- integernet_solr_update_query_text
- integernet_solr_before_search_request
- integernet_solr_after_search_request
- integernet_solr_product_collection_load_before
- integernet_solr_product_collection_load_after
- integernet_solr_can_index_product

Weitere Informationen zu den Events, ihren Parametern und Nutzungszwecken, ebenso wie ein Beispielmodul finden Sie in unserem [Blogpost](https://www.integer-net.de/nutzung-von-events-in-integernet_solr-ein-beispiel/).

Kommandozeilen-Befehle
---------------------
Auf der Kommandozeile haben wir mehrere Befehle bereit gestellt, die Sie für die Indizierung nutzen können:

- `bin/magento indexer:reindex integernet_solr`: Reindiziert alle Produkte für alle Stores.
- `bin/magento indexer:reindex integernet_solr_categories`: Reindiziert alle Kategorien für alle Stores.
- `bin/magento solr:reindex:full`: Reindiziert alle Produkte für einen oder mehrere Stores.
- `bin/magento solr:reindex:slice`: Reindiziert einen Teil der Produkte für einen oder mehrere Stores. Die Anzahl der Teile („Slices“) und den zu indizierenden Teil können Sie über Parameter bestimmen. Diese Funktion kann für parallele Indizierung mit mehreren Prozessen genutzt werden.
- `bin/magento solr:clear`: Löscht alle Produktdaten aus dem Solr-Core (oder dem Swap Core) für einen oder mehrere Stores.
- `bin/magento solr:swap`: Tauscht den Live-Core mit dem Swap Core. Dies ist nur bei Verwendung der Slice-Funktionalität notwendig.

Weitere Informationen finden Sie in den Hilfetexten zu den Befehlen, die Sie z.B. mit `bin/magento solr:swap –help` aufrufen können.

Mögliche Probleme und Lösungsansätze
-------------------------------------
1. **Das Speichern von Produkten im Backend dauert lange**
    Das kann passieren, wenn Sie viele Store Views haben, da für die beim Speichern stattfindende Indizierung für jeden Store View eine eigene Anfrage an Solr gesendet werden muss. Wir empfehlen, in diesem Fall den Indizierungs-Modus des `integernet_solr`-Index auf "manuell" zu stellen und jede Nacht eine komplette Reindizierung per Cronjob vorzunehmen, wenn möglich.

2. **Die Produktdaten im Suchvorschaufenster sollten für verschiedene Kundengruppen unterschiedlich aussehen, sehen aber überall gleich aus**
    Da der produktabhängige HTML-Code immer im Solr-Index gespeichert wird, ist das leider nicht möglich. Versuchen Sie, das HTML in `template/integernet/solr/autosuggest/item.phtml` so anzupassen, dass es keine kundenspezifischen Informationen (z. B. Preise) mehr enthält.
