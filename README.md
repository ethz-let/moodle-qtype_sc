# Single Choice (ETH)

![](https://github.com/ethz-let/moodle-qtype_sc/actions/workflows/moodle-ci.yml/badge.svg)

## What it is:
Single Choice (ETH) is a Moodle questiontype, it can most likely be compared to Multichoice but with a difference. In contrary to Multichoice students do not only have the choice to give the right answer by selecting the correct option row but also by marking incorrect option rows (distractors) as wrong. Therefore Single Choice (ETH) comes with three different grading methods which make more or less use of the distractor mechanics. While SC 1/0 is almost identical to Multichoice in terms of question mechanics the other two grading methods also include distractors but handle results slightly different.

## The Scoringmethods explained:
- **Scoring method: SC1/0**
   The student receives full points for selecting the correct answer, and zero points otherwise.

- **Scoringmethod: Aprime**
   The student receives full points for selecting the correct answer or for selecting all distractors correctly, half of the points if one correct distractor remains unchecked and zero points otherwise.
  
- **Scoringmethod: Subpoints**
   The student receives full points for selecting the correct answer or for selecting all distractors correctly, half of the points if one correct distractor remains unchecked and a fraction of points otherwise.

## Installation:
1. Extract the contents of the downloaded zip to `question/type/`.
1. Rename the extracted folder to `sc`.
1. Start the Moodle upgrade procedure.

## Migration Scripts:

#### qtype_multichoice to qtype_sc
##### Description:
The Script bin/mig_multichoice_to_sc.php migrates questions of the type 
qtype_multichoice to the questiontype qtype_sc. No questions will 
be overwritten or deleted, the script will solely create new questions.

##### Required Parameters (choose 1):
 - courseid (values: a valid course ID)
 - categoryid (values: a valid category ID)
 - all (values: 1)

##### Conditional Parameters (choose 0-n):
 - dryrun (values: 0,1)
 - includesubcategories (values: 0,1)

    The Dryrun Option is enabled (1) by default.
    With Dryrun enabled no changes will be made to the database.
    Use Dryrun to receive information about possible issues before 
    migrating.

    includesubcategories can only be used in combination with the
    migration by "categoryid".
    If activated (1) subcategories will be included in migration.

##### Examples

 - Migrate Multichoice Questions in a specific course:
   ```
   MOODLE_URL/question/type/sc/bin/mig_multichoice_to_sc.php?courseid=55
   ```
 - Migrate Multichoice Questions in a specific category:
   ```
   MOODLE_URL/question/type/sc/bin/mig_multichoice_to_sc.php?categoryid=1
   ```
 - Migrate all Multichoice Questions:
    ```
   MOODLE_URL/question/type/sc/bin/mig_multichoice_to_sc.php?all=1
   ```
 - Disable Dryrun:
   ```
   MOODLE_URL/question/type/sc/bin/mig_multichoice_to_sc.php?all=1&dryrun=0
   ```
   
#### qtype_sc to qtype_multichoice
##### Description:
The Script bin/mig_sc_to_multichoice.php migrates questions of the type 
qtype_sc to the questiontype qtype_multichoice. No questions will be overwritten 
or deleted, the script will solely create new questions.

##### Required Parameters (choose 1):
 - courseid (values: a valid course ID)
 - categoryid (values: a valid category ID)
 - all (values: 1)

##### Conditional Parameters (choose 0-n):
 - dryrun (values: 0,1)
 - includesubcategories (values: 0,1)

    The Dryrun Option is enabled (1) by default.
    With Dryrun enabled no changes will be made to the database.
    Use Dryrun to receive information about possible issues before 
    migrating.

    includesubcategories can only be used in combination with the
    migration by "categoryid".
    If activated (1) subcategories will be included in migration.

##### Examples

 - Migrate SC Questions in a specific course:
   ```
   MOODLE_URL/question/type/sc/bin/mig_sc_to_multichoice.php?courseid=55
   ```
 - Migrate SC Questions in a specific category:
   ```
   MOODLE_URL/question/type/sc/bin/mig_sc_to_multichoice.php?categoryid=1
   ```
 - Migrate all SC Questions:
   ```
   MOODLE_URL/question/type/sc/bin/mig_sc_to_multichoice.php?all=1
   ```
 - Disable Dryrun:
   ```
   MOODLE_URL/question/type/sc/bin/mig_sc_to_multichoice.php?all=1&dryrun=0
   ```

## Further information:
### Behat- and Unit tests:
Behat tests are included but scenarios are designed explicitly for ETH Zürich testcases.
Some of the included Test steps are designed to work with the ETH Zürich Moodle setup.
However Unit tests can be used genetically in combination with any Moodle setup.
 
## Contributors:
ETH Zürich (Lead maintainer)
Antonia Bonaccorso (Service owner, antonia.bonaccorso@id.ethz.ch)
