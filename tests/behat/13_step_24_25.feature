@qtype @qtype_sc @qtype_sc_add @qtype_sc_step_24_25
Feature: Step 24 and 25

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
      | student1 | S1        | Student1 | student1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
      | student1 | c1     | student        |

 @javascript
  Scenario: TESTCASE 24.
  # Testcase 24:
  # When adding hint options, hints should be saved.
  # Hints should also be duplicated if the question is duplicated

  # Create a question with hints
    And I output "[SC - TESTCASE 24 - begin]"
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"
    And I press "Create a new question ..."
    And I click on "item_qtype_sc" "radio"
    And I press "Add"
    And I set the following fields to these values:
      | id_name               | SC-Question-001          |
      | id_defaultmark        | 1                        |
      | id_questiontext       | This is the questiontext |
      | id_generalfeedback    | This feedback is general |
      | id_option_1           | Question Text 1          |
      | id_feedback_1         | Feedback Text 1          |
      | id_option_2           | Question Text 2          |
      | id_feedback_2         | Feedback Text 2          |
      | id_option_3           | Question Text 3          |
      | id_feedback_3         | Feedback Text 3          |
      | id_correctrow_1       | checked                  |
      | id_hint_0             | Hint 1 should be saved   |
      | id_hint_1             | Hint 2 should be saved   |
    And I press "id_submitbutton"

  # Check if hints are saved
    When I click on "Edit" "link" in the "SC-Question-001" "table_row"
    And I click on "Multiple tries" "link"
    Then I should see "Hint 1 should be saved"
    And I should see "Hint 2 should be saved"
    And I press "id_submitbutton"

  # Duplicate question and see if hints are copied as well
    When I click on "Duplicate" "link" in the "SC-Question-001" "table_row"
    And I press "id_submitbutton"
    Then I should see "SC-Question-001 (copy)" 

  # Check if hints are saved
    When I click on "Edit" "link" in the "SC-Question-001 (copy)" "table_row"
    And I click on "Multiple tries" "link"
    Then I should see "Hint 1 should be saved"
    And I should see "Hint 2 should be saved"
    And I press "id_submitbutton"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I output "[SC - TESTCASE 24 - end]"


 @javascript
  Scenario: TESTCASE 25.
  # Testcase 25:
  # Hints are displayed as output and the penalty for displaying
  # those hints will be considered when computing the final score

  # Actvate Hints as teacher1
    And I output "[SC - TESTCASE 25 - begin]"
    Given the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel         | reference      | name                 |
      | Course               | c1             | Default for c1       |
    And the following "questions" exist:
      | questioncategory     | qtype          | name                 | template        |
      | Default for c1       | sc             | SC-Question-004      | question_four   |
    And quiz "Quiz 1" contains the following questions:
      | question         | page |
      | SC-Question-004  | 1    |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Edit settings" "link"
    And I click on "Question behaviour" "link"
    And I set the field "How questions behave" to "Interactive with multiple tries"
    And I press "Save and display"
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # Log in as student1 and solve quiz
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"

  # Correct answer, but 2 tries (1.00 - 0.33 - 0.33 = 0.33) (Subpoints)
    And I click on css "tr:contains('Option Text 5') label[title='Click to choose as correct option.']"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    Then I should see "This is the 2nd hint"
    And I press "Try again"
    And I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 0.33 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"
 
  # Correct answer, but 1 try (1.00 - 0.33 = 0.66) (Subpoints)
    And I click on css "tr:contains('Option Text 5') label[title='Click to choose as correct option.']"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 0.67 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Correct answer, and no further tries (1.00) (Subpoints)
    And I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Partially correct answer, and no further tries (0.50) (Subpoints)
    And I click on css "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Partially correct')" should exist
    And I should see "Mark 0.50 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Incorrect answer, and 2 tries (0.0 - 0.33 - 0.33 = 0.00) (Subpoints)
    And I click on css "tr:contains('Option Text 5') label[title='Click to choose as correct option.']"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    Then I should see "This is the 2nd hint"
    And I press "Try again"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Incorrect')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Incorrect answer, and 1 try (0.0 - 0.33 = 0.00) (Subpoints)
    And I click on css "tr:contains('Option Text 5') label[title='Click to choose as correct option.']"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Incorrect')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # Change Grading to SC 1/0
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Question bank" "link"
    And I click on "Edit" "link" in the "SC-Question-004" "table_row"
    And I click on "id_scoringmethod_sconezero" "radio"
    And I press "id_submitbutton"
    And I click on css ".usermenu"
    And I click on "Log out" "link" 

  # Log in as student1 and solve quiz
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Re-attempt quiz"

  # Correct answer, and no further tries (1.00) (SC1/0)
    And I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Incorrect answer, and 1 try (0.00 - 0.33 = 0) (SC1/0)
    And I click on css "tr:contains('Option Text 5') label[title='Click to choose as correct option.']"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Incorrect')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I click on css ".usermenu"
    And I click on "Log out" "link"


  # Change Grading to Aprime
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Question bank" "link"
    And I click on "Edit" "link" in the "SC-Question-004" "table_row"
    And I click on "id_scoringmethod_aprime" "radio"
    And I press "id_submitbutton"
    And I click on css ".usermenu"
    And I click on "Log out" "link"

  # Log in as student1 and solve quiz
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Re-attempt quiz"

  # Correct answer, and no further tries (1.00) (Aprime)
    And I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Correct')" should exist
    And I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Incorrect answer, and 1 try (0.00 - 0.33 = 0) (Aprime)
    And I click on css "tr:contains('Option Text 5') label[title='Click to choose as correct option.']"
    And I press "Check"
    Then I should see "This is the 1st hint"
    And I press "Try again"
    And I press "Check"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then element with css ".state:contains('Incorrect')" should exist
    And I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I output "[SC - TESTCASE 25 - end]"



