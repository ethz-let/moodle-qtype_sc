# Single Choice (ETH)

## What it is:
The MC question type as it exists in Moodle is not very ideal, since you can configure questions which are not very well designed regarding best practices of MC questions. Therefore two question types the Mutliple True/False and the Single Choice will be developed. Creating a SC question teachers will have the possibility of choosing between three scoring methods:
- SC 1/0
- Subpoints 
- Aprime

Aprime is a new scoring method designed to find a solution for a fairer scoring if students are risk averse.

## The Scoringmethods explained:
- **Scoring method: SC1/0**
   A student solving a SC question clicks on the distractor icon to select distractors. In this way she or he can cross out the options, which she/he is sure are not the correct answers and doing this she/he can focus on finding the correct option between the remaining not crossed out options. This selection has to be saved for the duration of the exam and in the results.

   **SC run mode with distractor cross out (scoring methods: SC1/0)**
   This feature allows the student to cross out options he or she is sure are wrong so that he or she only has to look at the reminding options coming back again to solve the question. This allows what student can do on paper exams, when marking distractors with a pencil.

- **Scoringmethod: Aprime or Subpoints**
   In the contrary to SC1/0 for the scoring method Aprime and Subpoints the crossing-out-distractors-function is combined with scoring. A student can cross out an option as a distractor instead of guessing which option is correct and gets subpoints for each correctly identified distractor. As soon she or he selects an option as the correct one, no subpoints are awarded for correctly identified distractors. The layout here has to show that this is used as a scoring method and not as a crossing out of distractors for annotation reasons.

## Installation:
1. Extract the contents of the downloaded zip to `question/type/`.
1. Rename the extracted folder to `sc`.
1. Start the Moodle upgrade procedure.

## Further information:
### Behat- and Unit tests:
Behat tests are included but scenarios are designed explicitly for ETH Zürich testcases.
Some of the included Test steps are designed to work with the ETH Zürich Moodle setup.
However Unit tests can be used genetically in combination with any Moodle setup.
 
## Contributors:
ETH Zürich (Lead maintainer)
Thomas Korner (Service owner, thomas.korner@let.ethz.ch)
