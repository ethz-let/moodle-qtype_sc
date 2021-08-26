@qtype @qtype_sc @qtype_sc_7
Feature: Step 7

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email              |
      | teacher  | T1        | teacher  | teacher@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | c1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name          | template       |
      | Default for c1   | sc    | SC Question 3 | question_four |
    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript @_switch_window
  Scenario: Testcase 9, 10, 11 - Part 1

  # Change scoring Method to SC1/0 and test evaluation.
  # If everything correct -> Max. Points
  # If one or more incorrect -> 0 Points

    When I choose "Edit question" action for "SC Question 3" in the question bank
    And I click on "id_scoringmethod_sconezero" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    And I press "Start again"
    And I click on "tr:contains('Option Text 2') label[title='Click to choose as correct option.']" "css_element"
    And I press "Check"
    Then I should see "Mark 0.00 out of 1.00"

  @javascript @_switch_window
  Scenario: Testcase 9, 10, 11 - Part 2

  # Change scoring Method to Subpoints and test evaluation.
  # For each correct answer you should get subpoints.
  # You should also get subpoints if you answer some correctly
  # but dont't fill out all options

    When I choose "Edit question" action for "SC Question 3" in the question bank
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I set the field "Marks" to "Show mark and max"
    And I press "Update display options"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Submit and finish"
    Then I should see "Mark 1.00 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Submit and finish"
    Then I should see "Mark 0.50 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Submit and finish"
    Then I should see "Mark 0.33 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Submit and finish"
    Then I should see "Mark 0.17 out of 1.00"

  @javascript @_switch_window
  Scenario: Testcase 9, 10, 11 - Part 3

  # Change scoring Method to aprime and test evaluation.

    When I choose "Edit question" action for "SC Question 3" in the question bank
    And I click on "id_scoringmethod_aprime" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I set the field "Marks" to "Show mark and max"
    And I press "Update display options"
    When I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Submit and finish"
    Then I should see "Mark 1.00 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Submit and finish"
    Then I should see "Mark 1.00 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Submit and finish"
    Then I should see "Mark 0.50 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Submit and finish"
    Then I should see "Mark 0.00 out of 1.00"
