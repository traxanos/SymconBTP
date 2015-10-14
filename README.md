# SymconBTP

SymconBTP ist eine Erweiterung für die Heimautomatisierung IP Symcon. Diese Erweiterung stellt eine alternative zum gängigen Geofancing dar. Dabei dient das Bluetoothgeräte (meist das Mobiltelefon) als Erkennungsmerkmal. Wird beim Scan ein Gerät mit der hinterlegten MAC-Adresse gefunden, so wird der Status auf Anwesend gesetzt. Zusäzlich wird zum Status noch das Datum des Zustandwechsel sowie der Gerätename gespeichert.

## Einrichtung

Die Einrichtung erfolgt über die Modulverwaltung von Symcon. Nach der Installation des Moduls sollte der Dienst neugestartet werden. Danach kann man pro Gerät eine Instanz vom Typ "Presence Device" anlegen.

## Einstellungen

* **MAC**  _Die MAC des Geräte_
* **Interval**  _In welchem Abstand soll nach dem Gerät gesucht werden_

## Voraussetzung

* Linux
* Bluez (sudo apt-get install bluez)
* Bluetooth Dongle
* ab Symcon Version 4

## Tipps & Trick

## Funktionen

	// Sucht nach dem Bluetoothdevice
	BTP_Scan($id);
	
