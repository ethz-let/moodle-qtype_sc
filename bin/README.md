Das Skript mig_mulitchoice_to_sc.php migriert Multiple-Choice Fragen (im single choice Modus) in
den Fragentyp qtype_sc. Es werden nur Multiple-Choice Fragen migriert, die in den Einstellungen 
"Nur eine Antwort erlauben" aktiviert haben. 
Migrierte Fragen werden mit Bewertungsmethode SC1/0 initiiert.

Das Skript mig_sc_to_mulitchoice.php migriert SC-Fragen in Multiple-Choice Fragen. 

Nur Website-Administratoren dürfen die Skripte ausführen.
Es werden keine Fragen überschrieben oder gelöscht, sondern immer nur neue Fragen erstellt.
Da keine Fragen gelöscht werden, kann die Migration beliebig oft wiederholt werden.
Es werden hierbei immer wieder neue SC Fragen hinzugefügt.


Die Skripte akzeptieren folgende Parameter in der URL:

 - courseid : Die Moodle ID des Kurses, auf den die Migration
   eingeschränkt werden soll. Default 0, d.h. keine Einschränkung.

 - categoryid: Die Moodle ID der Fragen-Kategory, auf den die Migration
   eingeschränkt werden soll. Default 0, d.h. keine Einschränkung.

 - all: Wenn 1, dann werden alle Fragen der Plattform migriert, ohne
   Einschränkungen.  Default 0.

 - dryrun: Wenn 1, dann werden keine neuen Fragen erstellt. Es wird nur
   Information über die zu migrierenden Fragen ausgegeben. Default 0.

 - includesubcategories: Wird in Kombination mit Migration by "categoryid"
   verwendet. Falls aktiviert (1) werden Unterkategorien mit migriert.

Ein Aufruf geschieht dann in einem Browser z.B. wiefolgt:
   <URL zum Moodle>/question/type/sc/bin/mig_multichoice_to_sc.php?courseid=12345&dryrun=1
oder 
   <URL zum Moodle>/question/type/sc/bin/mig_multichoice_to_sc.php?categoryid=56789&dryrun=1

   <URL zum Moodle>/question/type/sc/bin/mig_sc_to_multichoice.php?courseid=12345&dryrun=1
oder 
   <URL zum Moodle>/question/type/sc/bin/mig_sc_to_multichoice.php?categoryid=56789&dryrun=1


WICHTIG:
Wenn dryrun nicht angegeben wird (oder auf 0 gesetzt ist), wird die Migration durchgeführt.
