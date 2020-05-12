@qtype @qtype_sc @qtype_sc_step_1
Feature: Step 1

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  Scenario: TESTCASE 1.

    When I add a "Single Choice (ETH)" question filling the form with:
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
  Then I should see "SC-Question-001"