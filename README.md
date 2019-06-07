# Single Choice

## What it is:
The MC question type as it is in Moodle is not ideal, since questions can be configured in a way which is not very well designed regarding best practices of MC questions. 
Therefore two question types, the Mutliple True/False and the Single Choice have been developed.

Creating a SC question teachers will have the possibility of choosing between three scoring methods:
1. SC 1/0
1. Subpoints
1. Aprime
Aprime is a new scoring method designed to find a solution for a fairer scoring if students are risk averse.

## Installation:
1. Extract the contents of the downloaded zip to `question/type/`.
1. Rename the extracted folder to `sc`.
1. Start the Moodle upgrade procedure.

## Further information:
### Behat- and Unit tests:
Behat tests are included but scenarios are designed explicitly for ETH Zürich testcases.
Some of the included Test steps are designed to work with the ETH Zürich Moodle setup.
However Unit tests can be used genetically in combination with any Moodle setup.

### Moodle coding style:
This is currently wip and will be updated in the near future.
 
## Contributors:
ETH Zürich (Lead maintainer)
Thomas Korner (Service owner, thomas.korner@let.ethz.ch)

## moodle-qtype_sc created for ETH Zürich by eDaktik GmbH
 * @package qtype_sc
 * @author        Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author        Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright     2017 eDaktik GmbH {@link http://www.edaktik.at}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later