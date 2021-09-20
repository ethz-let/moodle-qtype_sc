@qtype @qtype_sc @qtype_sc_1
Feature: Step 1

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
      | Course       | C1        | AnotherCat     |
    And I log in as "admin"

  @javascript @_switch_window @_alert
  Scenario: Testcase 2, 6, 7, 12

    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_sc" to "1"
    And I press "submitbutton"
    And I should see "Adding a Single Choice"
    Then I set the following fields to these values:
      | id_name     | SC Question 1 |
      | id_option_1 | Option 1      |
      | id_option_2 | Option 2      |
      | id_option_3 | Option 3      |
    And I press "id_updatebutton"

  # Checking behavior for 3 options
    And the following fields match these values:
      | Number of options | 3 |
    And I should see "Option 1"
    And I should see "Option 2"
    And I should see "Option 3"
    And I should not see "Option 4"
    And I should not see "Option 5"
    When I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Option 1"
    And I should see "Option 2"
    And I should see "Option 3"
    And I should not see "Option 4"
    And I should not see "Option 5"
    And I switch to the main window

  # Checking behavior for 4 options
    When I set the field "Number of options" to "4"
    And I wait "1" seconds
    Then I should see "Option 4"
    And I should not see "Option 5"
    And I set the following fields to these values:
      | id_option_4 | Option 4      |
    And I press "id_updatebutton"
    When I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Option 1"
    And I should see "Option 2"
    And I should see "Option 3"
    And I should see "Option 4"
    And I should not see "Option 5"
    And I switch to the main window

  # Checking behavior for 5 options
    When I set the field "Number of options" to "5"
    And I wait "1" seconds
    Then I should see "Option 4"
    And I should see "Option 5"
    And I set the following fields to these values:
      | id_option_5 | Option 5      |
    And I press "id_updatebutton"
    When I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Option 1"
    And I should see "Option 2"
    And I should see "Option 3"
    And I should see "Option 4"
    And I should see "Option 5"
    And I switch to the main window

  # Checking behavior for 2 options
    When I click on "select[id='id_numberofrows'] option[value='2']" "css_element" confirming the dialogue
    And I wait "1" seconds
    Then I should see "Option 1"
    And I should see "Option 2"
    And I should not see "Option 3"
    And I should not see "Option 4"
    And I should not see "Option 5"
    And I press "id_updatebutton"
    When I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Option 1"
    And I should see "Option 2"
    And I should not see "Option 3"
    And I should not see "Option 4"
    And I should not see "Option 5"
    And I switch to the main window

  # Change correct option
    When I set the following fields to these values:
      | id_correctrow_1 | checked |
    And I press "id_updatebutton"
    Then I set the following fields to these values:
      | id_correctrow_1 | checked |
    When I set the following fields to these values:
      | id_correctrow_2 | checked |
    And I press "id_updatebutton"
    Then I set the following fields to these values:
      | id_correctrow_2 | checked |
    And I log out

  @javascript
  Scenario: Testcase 1, 3

  # Create question and check if all values are on default state
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_sc" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Single Choice"
    When I expand all fieldsets
    Then the following fields match these values:
      | id_name                    ||
      | id_defaultmark             | 1 |
      | id_questiontext            | Enter the stem or question prompt here. |
      | id_generalfeedback         ||
      | id_scoringmethod_sconezero | checked |
      | id_option_1                ||
      | id_feedback_1              ||
      | id_option_2                ||
      | id_feedback_2              ||
      | id_option_3                ||
      | id_feedback_3              ||
      | id_correctrow_1            | checked |
      | id_hint_0                  ||
      | id_hint_1                  ||
    And I should see "No selection" in the "#fitem_id_tags" "css_element"

  @javascript
  Scenario: (new0)

  # Create a question filling out all forms
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I add a "Single Choice (ETH)" question filling the form with:
      | id_name                  | SC Question               |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_1              | 1st optiontext            |
      | id_feedback_1            | 1st feedbacktext          |
      | id_option_2              | 2nd optiontext            |
      | id_feedback_2            | 2nd feedbacktext          |
      | id_option_3              | 3rd optiontext            |
      | id_feedback_3            | 3rd feedbacktext          |
      | id_correctrow_3          | checked                   |
      | id_hint_0                | 1th hinttext              |
      | id_hint_1                | 2nd hinttext              |
      | Tags                     | Tag1, Tag2                |
    Then I should see "SC Question"

  # Open the saved question and check if everything has been saved
    When I choose "Edit question" action for "SC Question" in the question bank
    Then the following fields match these values:
      | id_name                  | SC Question               |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_1              | 1st optiontext            |
      | id_feedback_1            | 1st feedbacktext          |
      | id_option_2              | 2nd optiontext            |
      | id_feedback_2            | 2nd feedbacktext          |
      | id_option_3              | 3rd optiontext            |
      | id_feedback_3            | 3rd feedbacktext          |
      | id_correctrow_3          | checked                   |
      | id_hint_0                | 1th hinttext              |
      | id_hint_1                | 2nd hinttext              |
    And I should see "Tag1" in the "#fitem_id_tags" "css_element"
    And I should see "Tag2" in the "#fitem_id_tags" "css_element"

  @javascript
  Scenario: Testcase

  # Create a question and check if question title is required
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_sc" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Single Choice"
    When I set the following fields to these values:
      | id_name     |                |
      | id_option_1 | 1st optiontext |
    And I press "id_submitbutton"
    Then "#id_name.is-invalid" "css_element" should exist
    Then I should not see "You must enter an option text."

  # Enter question title and check if stem is required
    When I set the following fields to these values:
      | id_name        | SC Question |
      | id_defaultmark |             |
    And I press "id_submitbutton"
    Then "#id_name.is-invalid" "css_element" should not exist
    And "#id_defaultmark.is-invalid" "css_element" should exist

  # Enter defaultmark and check if stem is required
    When I set the following fields to these values:
      | id_defaultmark  | 1 |
      | id_questiontext |   |
    And I press "id_submitbutton"
    Then "#id_defaultmark.is-invalid" "css_element" should not exist
    And I should see "You must supply a value here."

  # Enter stem and check if options are required
    When I set the following fields to these values:
      | id_questiontext | This is a questiontext. |
      | id_option_1     |                         |
    And I press "id_submitbutton"
    Then "#id_error_defaultmark" "css_element" should exist
    And I should not see "You must supply a value here."
    And I should see "You must enter an option text."

  # Enter everything correctly and check if question can be created as usual
    When I set the following fields to these values:
      | id_name     | SC Question     |
      | id_option_1 | 1st optiontext  |
      | id_option_2 | 2nd optiontext  |
      | id_option_3 | 3rd optiontext  |
    And I press "id_submitbutton"
    Then I should see "SC Question"

  @javascript
  Scenario: Testcase 1

  # Create a question and check if scoringmethod is default
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_sc" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Single Choice"
    When I click on "Scoring method" "link"
    Then "#id_scoringmethod_sconezero[checked]" "css_element" should exist

  # Change default scoringmethod in Plugin administration
    When I navigate to "Plugins > Question types > Single Choice (ETH)" in site administration
    And I should see "Administration settings for the single choice (ETH) question type."
    And I select "Subpoints" from the "s_qtype_sc_scoringmethod" singleselect
    And I press "Save changes"

  # Create a question and check if default scoringmethod has changed
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_sc" to "1"
    And I press "submitbutton"
    And I should see "Adding a Single Choice"
    And I click on "Scoring method" "link"
    Then "#id_scoringmethod_subpoints[checked]" "css_element" should exist

  @javascript
  Scenario: Testcase 4,5

    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I add a "Single Choice (ETH)" question filling the form with:
      | id_name                  | SC Question               |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_1              | 1st optiontext            |
      | id_feedback_1            | 1st feedbacktext          |
      | id_option_2              | 2nd optiontext            |
      | id_feedback_2            | 2nd feedbacktext          |
      | id_option_3              | 3rd optiontext            |
      | id_feedback_3            | 3rd feedbacktext          |
      | id_option_4              | 4th optiontext            |
      | id_feedback_4            | 4th feedbacktext          |
      | id_correctrow_1          | checked                   |
    Then I should see "SC Question"

  # Duplicate the question
    When I choose "Duplicate" action for "SC Question" in the question bank
    And I press "id_submitbutton"
    Then I should see "SC Question"
    And I should see "SC Question (copy)"

  # Move the question to another category
    When I click on "SC Question" "checkbox" in the "SC Question" "table_row"
    And I set the field "Question category" to "AnotherCat"
    And I press "Move to >>"
    Then I should see "Question bank"
    And I should see "AnotherCat"
    And I should see "SC Question"

  # Delete the question
    When I choose "Delete" action for "SC Question" in the question bank
    And I press "Delete"
    Then I should not see "SC Question"
