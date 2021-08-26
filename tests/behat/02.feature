@qtype @qtype_sc @qtype_sc_2
Feature: Step 2

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype  | name          | template     |
      | Test questions   | sc     | SC Question 1 | question_one |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript
  Scenario: Testcase 24

  #Export question
    When I navigate to "Question bank > Export" in current page administration
    And I set the field "id_format_xml" to "1"
    And I press "Export questions to file"
    And following "click here" should download between "1500" and "2000" bytes
    And I log out

  @javascript @_file_upload
  Scenario: Testcase 24

  # Import question

    And I navigate to "Question bank" in current page administration
    And I click on "Import" "link"
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/sc/tests/fixtures/testquestion.moodle.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I press "Continue"

    And I should see "SC-Question-001"
    When I choose "Preview" action for "SC-Question-001" in the question bank
    And I switch to "questionpreview" window
    Then "[alt='testimage1AltDescription']" "css_element" should exist
    And I should not see "testimage1AltDescription"
    And "[alt='testimage2AltDescription']" "css_element" should exist
    And I should not see "testimage2AltDescription"
    And I should see "Option Text 1"
    And I should see "Option Text 2"
    When I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Check"
    Then I should see "Feedback Text 1"
    And I should see "Option Text 1: Correct"
    And I should see "Option Text 2: Not correct"
