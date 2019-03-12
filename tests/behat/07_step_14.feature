@qtype @qtype_sc @qtype_sc_step_14
Feature: Step 14

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
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name           | template     |
      | Default for c1   | sc    | SC-Question-1  | question_one |
      | Default for c1   | sc    | SC-Question-2  | question_one |
      | Default for c1   | sc    | SC-Question-3  | question_one |
      | Default for c1   | sc    | SC-Question-5  | question_one |
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | SC-Question-1  | 1    |
      | SC-Question-2  | 2    |
      | SC-Question-3  | 3    |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I click on "Actions menu" "link"
    And I click on "Edit quiz" "link"


    And I should see "Editing quiz: Quiz 1"

  @javascript
  Scenario: TESTCASE 14
  # Create a quiz with SC Questions. Moving, deleting,
  # adding questions in the quiz. Preview quiz.
  # All should work like standard questions.

  # Repaginate
    And I output "[SC - TESTCASE 14 - begin]"
    When I press "Repaginate"
    Then I should see "Repaginate with"
    And I set the field "menuquestionsperpage" to "2"
    When I press "Go"
    And I should see "SC-Question-1" on quiz page "1"
    And I should see "SC-Question-2" on quiz page "1"
    And I should see "SC-Question-3" on quiz page "2"

  # Add a new question to the quiz
    And I click on css "li:contains('Page 2') .add-menu-outer"
    And I click on css ".menu-action-text:contains('a new question')"
    And I set the field "item_qtype_sc" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Single Choice"
    When I set the following fields to these values:
      | id_name               | SC-Question-4          |
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
    And I press "id_submitbutton"
    Then I should see "Editing quiz: Quiz 1"
    And I should see "SC-Question-4"

  # Add a question from the question bank to the quiz
    And I click on css "li:contains('Page 2') .add-menu-outer"
    And I click on css ".menu-action-text:contains('from question ban')"
    And I click on "Add to quiz" "link" in the "SC-Question-5" "table_row"
    Then I should see "Editing quiz: Quiz 1"
    And I should see "SC-Question-5"

  # Delete a question from a quiz
    When I click on "Delete" "link" in the "SC-Question-4" "list_item"
    And I click on "Yes" "button" in the ".moodle-dialogue-wrap" "css_element" 
    Then I should see "Editing quiz: Quiz 1"
    And I should not see "SC-Question-4"
    And I output "[SC - TESTCASE 14 - end]"
 

