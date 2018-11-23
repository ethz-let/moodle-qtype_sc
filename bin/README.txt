Das Skript mig_mulitchoice_to_sc.php migriert alte ETHZ Multiple-Choice Fragen (im single choice Modus) in
den neuen Fragentyp qtype_sc. Es werden keine Fragen überschrieben
oder gelöscht, sondern immer nur neue Fragen erstellt. Es werden nur
Multiple-Choice Fragen migriert, die in den Einstellungen "Nur eine Antwort erlauben" aktiviert haben.

Nur Website-Administratoren dürfen das Skript ausführen. 

Das Skript akzeptiert folgende Parameter in der URL:

 - courseid : Die Moodle ID des Kurses, auf den die Migration
   eingeschränkt werden soll. Default 0, d.h. keine Einschränkung.

 - categoryid: Die Moodle ID der Fragen-Kategory, auf den die Migration
   eingeschränkt werden soll. Default 0, d.h. keine Einschränkung.

 - dryrun: Wenn 1, dann werden keine neuen Fragen erstellt. Es wird nur
   Information über die zu migrierenden Fragen ausgegeben. Default 0.

 - all: Wenn 1, dann werden alle Fragen der Plattform migriert, ohne
   Einschränkungen.  Default 0.

Ein Aufruf geschieht dann in einem Browser z.B. wiefolgt:
   <URL zum Moodle>/question/type/sc/bin/mig_multichoice_to_sc.php?courseid=12345&dryrun=1
oder 
   <URL zum Moodle>/question/type/sc/bin/mig_multichoice_to_sc.php?categoryid=56789&dryrun=1

Sobald dryrun nicht angegeben wird (oder auf 0 gesetzt wird), wird die
Migration durchgeführt. Da keine Fragen gelöscht werden, kann die
Migration beliebig oft wiederholt werden. Es werden dann immer wieder
neue SC Fragen hinzugefügt.

Als Bewertungsmethode wird SC1/0 gewaehlt.
