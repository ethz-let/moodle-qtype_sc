@qtype @qtype_sc @qtype_sc_step_8_9_10
Feature: Step 8 and Step 9 and Step 10

  Background:
    Given the following "users" exist:
      | username             | firstname      | lastname         | email               |
      | teacher1             | T1             | Teacher1         | teacher1@moodle.com |
      | student1             | S1             | Student1         | student1@moodle.com |
    And the following "courses" exist:
      | fullname             | shortname      | category         |
      | Course 1             | c1             | 0                |
    And the following "course enrolments" exist:
      | user                 | course         | role             |
      | teacher1             | c1             | editingteacher   |
      | student1             | c1             | student          |
    And the following "activities" exist:
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
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"


  @javascript @_switch_window
  Scenario: TESTCASE 8
  # Change scoring Method to SC1/0 and test evaluation.
  # If everything correct -> Max. Points
  # If one or more incorrect -> 0 Points

  # Set Scoring method to sconezero
    And I output "[SC - TESTCASE 8 - begin]"
    When I click on "Edit" "link" in the "SC-Question-004" "table_row"
    And I click on "id_scoringmethod_sconezero" "radio"
    And I press "id_submitbutton"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"

  # Test quiz with scoringmethod "sconezero" -> select correct answer 
    When I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Test quiz with scoringmethod "sconezero" -> select incorrect answer
    When I click on css "tr:contains('Option Text 2') label[title='Click to choose as correct option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 0.00 out of 1.00"
    And I output "[SC - TESTCASE 8 - end]"
 
  @javascript @_switch_window
  Scenario: TESTCASE 9
  # Change scoring Method to Subpoints and test evaluation.
  # When wrong answers are crossed out as incorrect you receive points

    And I output "[SC - TESTCASE 9 - begin]"
  # Set Scoring method to subpoints
    When I click on "Edit" "link" in the "SC-Question-004" "table_row"
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_submitbutton"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"

  # Test quiz with scoringmethod "subpoints" -> select correct answer
    When I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Test quiz with scoringmethod "subpoints" -> cross out 2 incorrect options
    And I click on css "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 0.33 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Test quiz with scoringmethod "subpoints" -> cross out 3 incorrect options
    And I click on css "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 0.50 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Test quiz with scoringmethod "subpoints" -> cross out the correct option
    And I click on css "tr:contains('Option Text 1') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 0.00 out of 1.00"
    And I output "[SC - TESTCASE 9 - end]"

 @javascript @_switch_window
  Scenario: TESTCASE 10
  # Change scoring Method to Aprime and test evaluation.
  # When wrong answers are crossed out as incorrect you receive points

    And I output "[SC - TESTCASE 10 - begin]"
  # Set Scoring method to Aprime
    When I click on "Edit" "link" in the "SC-Question-004" "table_row"
    And I click on "id_scoringmethod_aprime" "radio"
    And I press "id_submitbutton"
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"

  # Test quiz with scoringmethod "aprime" -> select correct answer
    When I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 1.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Test quiz with scoringmethod "aprime" -> cross out 3 (enough) incorrect options
    And I click on css "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 0.50 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Test quiz with scoringmethod "aprime" -> cross out 3 (not enough) incorrect options
    And I click on css "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 0.00 out of 1.00"
    And I click on "Finish review" "link"
    And I press "Re-attempt quiz"

  # Test quiz with scoringmethod "aprime" -> cross out the correct option
    And I click on css "tr:contains('Option Text 1') label[title='Click to cross out as incorrect option.']"
    And I click on css "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Mark 0.00 out of 1.00"
    And I output "[SC - TESTCASE 10 - end]"

