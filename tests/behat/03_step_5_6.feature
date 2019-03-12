@qtype @qtype_sc @qtype_sc_step_5_6
Feature: Step 5 and Step 6

  Background:
    Given the following "users" exist:
      | username             | firstname      | lastname         | email               |
      | teacher1             | T1             | Teacher1         | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname             | shortname      | category         |
      | Course 1             | c1             | 0                |
    And the following "course enrolments" exist:
      | user                 | course         | role             |
      | teacher1             | c1             | editingteacher   |
    And the following "question categories" exist:
      | contextlevel         | reference      | name             |
      | Course               | c1             | Default for c1   |
    And the following "questions" exist:
      | questioncategory     | qtype          | name            | template     |
      | Default for c1       | sc             | SC-Question-001 | question_one |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"

  @javascript
  Scenario: TESTCASE 5.
  # Add, change options within a SC question.
  # Option can be added and changed.

    And I output "[SC - TESTCASE 5 - begin]"
  # Change SC options
    When I click on "Edit" "link" in the "SC-Question-001" "table_row"
    And I set the following fields to these values:
      | id_option_1          | New Option Text 1    |
      | id_option_2          | New Option Text 2    |
      | id_option_3          | questiontext 3       |
      | id_feedback_1        | New Feedbacktext 1   |
      | id_feedback_2        | feedback 2           |
      | id_feedback_3        | feedback 3           |
      | id_correctrow_3      | checked              |
    And I press "id_submitbutton"
    Then I should see "SC-Question-001"
    When I click on "Edit" "link" in the "SC-Question-001" "table_row"
    Then I should see "New Option Text 1"
    And I should see "New Option Text 2"
    And I should see "questiontext 3"
    And I should see "New Feedbacktext 1"
    And I should see "feedback 2"
    And I should see "feedback 3"
    And element with css "#id_correctrow_3:checked" should exist

  # Add SC options
    When I set the field "id_numberofrows" to "5"
    And I set the following fields to these values:
      | id_option_4          | Option Text 4   |
      | id_option_5          | Option Text 5   |
      | id_feedback_4        | Feedback Text 4 |
      | id_feedback_5        | Feedback Text 5 |
    And I press "id_submitbutton"
    And I click on "Edit" "link" in the "SC-Question-001" "table_row"
    Then I should see "Option Text 4"
    And I should see "Option Text 5"
    And I should see "Feedback Text 4"
    And I should see "Feedback Text 5"

    And I output "[SC - TESTCASE 5 - end]"

  @javascript
  Scenario: TESTCASE 6.
  # Save with empty options
  # All options must be filled
    
    And I output "[SC - TESTCASE 6- begin]"
    When I click on "Edit" "link" in the "SC-Question-001" "table_row"
    And I set the following fields to these values:
      | id_option_1 | |
    And I press "id_submitbutton"
    Then I should see "You must enter an option text."
    And I set the following fields to these values:
      | id_option_1 | New Optiontext 1 |
    And I press "id_submitbutton"
    Then I should see "SC-Question-001"
    And I output "[SC - TESTCASE 6 - end]"


    


    



