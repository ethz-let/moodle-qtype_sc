@qtype @qtype_sc @qtype_sc_step_12
Feature: Step 12

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
      | questioncategory     | qtype          | name             | template            |
      | Default for c1       | sc             | SC-Question-001  | question_one        |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript
  Scenario: TESTCASE 12
  # Change the correct answer
  # There should always be one answer selected 

    When I choose "Edit question" action for "SC-Question-001" in the question bank
    And I click on "id_correctrow_1" "radio"
    And I press "id_updatebutton"
    Then "#id_correctrow_1[checked]" "css_element" should exist
    And "#id_correctrow_2:not([checked])" "css_element" should exist
    And "#id_correctrow_3:not([checked])" "css_element" should exist
    When I click on "id_correctrow_2" "radio"
    And I press "id_updatebutton"
    Then "#id_correctrow_2[checked]" "css_element" should exist
    And "#id_correctrow_1:not([checked])" "css_element" should exist
    And "#id_correctrow_3:not([checked])" "css_element" should exist
    When I click on "id_correctrow_3" "radio"
    And I press "id_updatebutton"
    Then "#id_correctrow_3[checked]" "css_element" should exist
    And "#id_correctrow_1:not([checked])" "css_element" should exist
    And "#id_correctrow_2:not([checked])" "css_element" should exist