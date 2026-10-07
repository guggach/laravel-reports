	1. Ausgangslage
Laravel ist hervorragend für webbasierte Seiten und CRUD Formulare. Diese sind in der Regel schlecht druckbar, keine Seitenvoranzeige und kein PDF Audruck bzw Speicherung.


	2. Idee Reportkomponente als Package für Laravel bauen
Ich möchte ein Package bauen, das man in Laravel mit dem Serviceprovider integrieren kann. Der Entwickler soll eine Report Klasse verwenden können, um damit durch ihn definierte Report erzeugen kann und das Ergbnis ins diversen Formaten ausgeben kann.

Der Entwickler speichert alle Reportdefinitionen per default in '/resources/reports'. Er kann in der config einen anderen base path definieren.

Ein Report hat folgende Prozesse:
	• Zeigt ein Filterformular (optional Filterdefinition kann auch mitgegeben werden)
	• Bezug der Daten und evt zusätzliche Aggregationsdaten für Gruppen
	• Rendering des Reports
	• Output im gewünschten Format 
	• Evt zusätzlich Speichern, wenn definiert oder im Aufruf verlangt 
	• Wenn weboutput dann erneuter Output ab display frame Buttons

	3. Definitionen
Jetzt wird es schwieriger. Wollen wir die einzelnen Teile Definieren. Ganz einfache Reports sollten in einem Definitionsfile wie XML oder Json möglich sein. Komplexe Reports wie zB ein Rechnungsdruck mit Positionen, Anzahlung und Einzahlungsschein benötige mehr Flexibilität. Hier sollte die Klasse vererbt werden. Er kann dann spezifische Unterklassen definieren und anhängen. Diese werden einzeln beschrieben. Grundsätzlich gibt es zwei Reportarten, wobei der technische Aufbau ähnlich ist, nämlich
	• Einzelformular, wobei die Felder über day Papier verteilt sind. Allenfalls gibt es untergeordnete loop wie Rechnungsposition. Der Fokus ist eine Rechnung.
	• Listen als loop über eine Datenmenge ähnlich einer Excelliste, wobei mehrzeiliger Output pro Detail möglich ist.

3.1 Zusammensetzung eines Reports
Jeder Report besteht aus Blöcken oder Bänder auf voller Breite. Ein Block kann in der Höhe fix oder flexibel definitiv werden (height oder min height)

3.1.1 Band Detail
Er steht im Zentrum. Bei dem neuen Datensatz gibt es eine Detail Block. In einer späteren Ausbaustufe könnte hier auch Subreports ohne Layout sein. Bei einer klasische Liste ist in diesem Block eine Gridzeile, wobei der Div mit der Griddefinition außerhalb ist.

Per default darf ein Detail block nicht mitten drin umgebrochen werden, ausser man lässt es per property zu oder wenn ein Brake page Tag kommt.

Oberhalb und unterhalb gibt es zusätzliche optionale Blöcke. Diese sind:

Page Header
Report Beginn title
Grid header per page
Groupe header Level 1
Groupe header level 2
Groupe header level n
Detail das hier beschrieben wurde
Groupe footer level n
Groupe footer level 2
Groupe footer level 1
Report end footer
Page footer


3.1.2 page header und page footer
Dieser block kommt auf allen Seite zuoberst. Dort könnte das Logo stehen oder der Name des Rechnungsempfängers ab der Seite 2. Es braucht properties wie hide first page, hide last page. Für gerade und ungerade Seiten  oder nur gewisse Seiten kann man Optional unterschiedliche header definieren. So könnte man den Standard Header auf Seite 1 ausblenden und explizit für die Seite 1 einen speziellen erfassen.

Die page header braucht es nur für seitenorientierte output wie Drucker oder PDF oder word. Endlose output wie Excel, HTML stream an den Browser brauchen ihn nicht. 

Meistens beginnt der header bei Position top 0,0 und man kann ihm eine fixe Höhe geben. Der page footer startet bei bottom 0,0.

Etwas komplexer sind die Seitenzahlen und noch schlimmer die Gesamtzahl der Seiten. Hier braucht es Platzhalter, die erst nach dem Rendern in einem nachloop gefüllt werden (siehe technische Hinweise für PDF.   


3.1.2 Report start title / end footer
Dieser Block ist optional. Im einfachsten Fall sind hier nur statische Elemente wie Titel oder Erklärungen. Der Block schließt direkt an den Page header bzw an den Groupe footer Level 1 an. 

Für komplexere Fälle kann man eine Datenklasse angeben, die eine Collection oder Array mit Datenfelder zum Anzeigen bringt. Es darf aber kein loop sein, dh, es darf nur 1 Record haben.

Zusätzlich können virtuelle Rechenfelder mit einem effektive Datenattribut definiert bzw registriert werden wie Addition, average etc., die bei jedem Detailrecord bearbeitet werden. Dafür braucht es für jeden Typ eine Klasse, die bei jeden Detail loop aufgerufen wird. Beispiel die Average Klasse bekommt einen Wert plus sie zählt die Aufrufe. Wenn man den Averagewert abruft dividiert sie die Summe durch count. Diese Felder stehen sowohl dem Header als auch Footer zur Verfügung. 

3.1.3 Grid header page
Wenn die Details am Ende angekommen sind, dann muss auf der neuen Seite zuerst der Tabellenkopf nochmals kommen, bevor es mit den Details weiter geht. Evt brauche es erneut ein div mit der Grid Definition. Wenn es das bräuchte, bräuchte es auf der alten Seite noch /div um den Gridblock zu schließen

3.1.4. Groupe header / footer level 1 bis n
Diese Bänder sind optional. Wenn man die Daten nach etwas sortiert erhält man oft zusammengehörige Blöcke von Daten. Beispiel ich habe zwei Zimmerkategorien, jede hat mehrere Preise je nach Dauer. Wenn ich die Preise als Details nehme, so kann ich nach Zimmerkategorien gruppieren. Ich könnte im Groupe header den Kategorie Namen ausgeben, dann kommen die Preise und im Group footer kommt die Anzahl plus average.

Immer wenn sich ein oder mehrere definerte Attribute änder wird dieser ausgelöst. Die Gruppen können verschachtelt werden.

Analog dem Report header / footer können datensets in der DB abgerufen werden oder man benutzt die virtuellen Felde wie Anzahl, Addition oder average.  

	4. Filter Formular
Der Entwickler kann bestimmen, welche Attribute im Filterformular angezeigt werden.

Die werden dem Anwender in einem Filterformular angezeigt. Der Anwender kann die Filterwerte eingeben und den Report auf diese Werte einschränken. Die Filterdefinitionen können in der Reportdefinition mitgegeben werden.

Das Filterformular hat für jeden Datentyp einen eigenen Filtergenerator, der standardmässig als Komponente angezeigt wird.

Die Liste der Attributstypen ist wie folgt:

	• Id Attribut, Foreign keys oder json keys: selector 'in' --> der user kann ein Formular mit einem Suchfeld einzelne Records suchen wie in eine Multiselectbox, um ein in Array aufzubauen. Natürlich wird dem User nicht die Id selbst in der Liste angezeigt, sondern ein Label Attribut, das in der Reportdefinition angegeben wird.

	• Strings: like --> der User kann Masken wie *meier* oder %meier%. Der Like ist immer case insensitive. Der User kann auch mehrere Werte eingeben, die mit OR verknüpft werden.

	• Numbers: Auswahl <=, >=, =, Between, Liste -->  der user kann den Vergleichsoperator auswählen und einen Wert eingeben. Bei between kommt ein zweiten Eingabefeld. Bei in kann der Benutzer eine Liste von Werten eingeben.

	• Boolean: ja oder nein oder beide . 

Habe ich einen Typ vergessen?

Eine Sortierung der Attribute ist möglich, so lange sie nicht Teil einer Gruppe sind. Die Kombination Filter und Sortierung kann der Benutzer unter einem Namen speichern. Der Benutzer kann wählen, ob das diese Speicherung nur für ihn oder alle Userr sichtbar ist. Der Entwickler oder Admin kann generelle presets speichern, die der Benutzer nicht löschen kann.

5.Technische Basis
Wenn wir modernes css wie grid nutzen möchten, brauchen wir einen headless Browser. Das Paket spatie/laravel pdf drängt sich hier auf. Nur die header und footer Funktion ist zu simpel. Also müssen wir die Höhen vorher in den Griff bekommen. Bei festen Bandhöhen können wir addieren. Bei flexiblen habe ich noch keine Lösung.
