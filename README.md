# SymconBTP
SymconBTP ist eine Erweiterung für die Heimautomatisierung IP Symcon. Diese Erweiterung stellt eine alternative zum gängigen Geofancing dar. Dabei dient das Bluetoothgeräte (meist das Mobiltelefon) als Erkennungsmerkmal. Wird beim Scan ein Gerät mit der hinterlegten MAC-Adresse gefunden, so wird der Status auf Anwesend gesetzt. Zusätzlich wird zum Status noch das Datum des Zustandwechsel sowie der Gerätename gespeichert.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Erkennung von Bluetooth Geräten
* Ermittlung seit das Gerät anwesend bzw. abwesend ist.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.0
- Linux
- Bluetooth Dongle
- Bluez (sudo apt-get install bluez)

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/traxanos/SymconBTP.git`  

### 4. Einrichten der Instanzen in IP-Symcon

Die Einrichtung erfolgt über die Modulverwaltung von Symcon. Nach der Installation des Moduls sollte der Dienst neugestartet werden. Danach kann man pro Gerät eine Instanz vom Typ "Presence Device" anlegen.

__Konfigurationsseite__:

Name            | Beschreibung
--------------- | ---------------------------------
MAC-Addresse    | Die MAC-Addrese z.B. vom Smartphone
Bluetooth LE    | Soll ein Spezieller Suchmodus verwendet werden (für manche TAGs nicht aber für Gerät wie Smartphones)
Interval        | In welchem Abstand sollen die Geräte gesucht werden. (in Sekunden)
Button "Suchen" | Suche die Bluetooth-Gerät

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name         | Typ       | Beschreibung
------------ | --------- | ----------------
Zustand      | Boolean   | Ist des Gerät anwesend?
Name         | String    | Names des Gerätes (nicht bei LE Scans)
Abwesend     | Integer   | Seit wann ist das Gerät abwesend. (Wird ausgeblendet wenn Anwesend)
Anwesend     | Integer   | Seit wann ist das Gerät anwesend. (Wird ausgeblendet wenn Abwesend)

##### Profile:

Es werden keine weiteren Profile benötigt.

### 6. WebFront

Zeigt den Zustand des Bluetoothgerät an.

### 7. PHP-Befehlsreferenz

`BTP_Scan(integer $InstanzID);`
Sucht das Gerät mit der InstanzID $InstanzID. Es kann nur 1 Scan parallel gestartet werden. Nach 5 Sekunden bricht der Scan ab falls noch ein anderer Scan läuft.
Die Funktion liefert keinerlei Rückgabewert.
`BTP_Scan(12345, false);`
