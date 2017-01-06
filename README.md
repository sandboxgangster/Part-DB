# Part-DB

### Beschreibung

Part-DB ist eine webbasierte Datenbank zum Verwalten von Elektronischen Bauteilen. Da der Zugriff über den Webbrowser erfolgt, muss Part-DB auf einem Webserver installiert werden. Danach kann die Software mit jedem gängigen Browser und Betriebssystem ohne Installation von Zusatzsoftware benutzt werden.

### Funktionen

 * Angabe von Lagerorten, Footprints, Kategorien, Lieferanten, Datenblattern, Preise, Bestellnummern, ...
 * Baugruppenverwaltung
 * Upload von Bauteil Bildern
 * Automatische Anzeige von Footprintbildern
 * Statistik über das gesamte Lager
 * Auflistung von: "Zu bestellende Teile", "Teile ohne Preis" und "nicht mehr erhältliche Teile"
 * Liste von Hersteller-Logos
 * Informationen zu SMD-Beschriftungen von Widerstände, Kondensatoren und Spulen
 * Widerstandsrechner
 * Verschiedene Designvarianten mitgeliefert
 * HTML5 basierte Weboberfläche, mobile Ansicht
 * BBCode kann zur Textauszeichnung innerhalb der Bauteile verwendet werden.
 * Sprachen: Deutsch und Englisch
 
### Anforderungen

 * Webserver mit ca. 60MB Platz (ohne Footprints)
 * PHP >= 5.5.0 mit mbtring und PDO (zusätzlich gettext wenn Übersetzungen gewünscht sind)
 * MySQL/MariaDB Datenbank

### Lizenz

Part-DB steht unter der GPL. Für externe Bibliotheken sehen sie bitte die Datei *EXTERNAL_LIBS.md* ein. 

Part-DB kann sowohl kommerziell als auch privat kostenfrei verwendet werden.

### Installationsanleitung & Dokumentation

Die gesamte Dokumentation inkl. Installationsanleitung gibts hier:
http://phpbookworm.singollo.de/project/part-db/documentation/dokuwiki/index.php

### Online-Demo zum Ausprobieren

Eine Test-Datenbank ist unter <http://part-db.bplaced.net/> zu finden.

