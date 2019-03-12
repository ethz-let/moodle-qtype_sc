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
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"


  @javascript
  Scenario: TESTCASE 12
  # Change the correct answer
  # There should always be one answer selected 

    And I output "[SC - TESTCASE 12 - begin]"
    When I click on "Edit" "link" in the "SC-Question-001" "table_row"
    And I click on "id_correctrow_1" "radio"
    And I press "id_updatebutton"
    Then element with css "#id_correctrow_1[checked]" should exist
    And element with css "#id_correctrow_2:not([checked])" should exist
    And element with css "#id_correctrow_3:not([checked])" should exist
    When I click on "id_correctrow_2" "radio"
    And I press "id_updatebutton"
    Then element with css "#id_correctrow_2[checked]" should exist
    And element with css "#id_correctrow_1:not([checked])" should exist
    And element with css "#id_correctrow_3:not([checked])" should exist
    When I click on "id_correctrow_3" "radio"
    And I press "id_updatebutton"
    Then element with css "#id_correctrow_3[checked]" should exist
    And element with css "#id_correctrow_1:not([checked])" should exist
    And element with css "#id_correctrow_2:not([checked])" should exist
    And I output "[SC - TESTCASE 12 - end]"
    
    
   
   

  
    
