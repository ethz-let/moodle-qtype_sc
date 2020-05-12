@qtype @qtype_sc @qtype_sc_step_18_19_20
Feature: Step 18 and 19 and 20

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
      | student2 | Student   | Tneduts  | student2@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
      | student2 | c1     | student        |   
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name           | template       |
      | Default for c1   | sc    | SC-Question-2  | question_one   |
      | Default for c1   | sc    | SC-Question-3  | question_two   |

  @javascript
  Scenario: TESTCASE 18.
  # In the first Run feedback will be enabled. Check if fb and results are displayed
  # In the second Run feedback will be disabled. Check if fb and results are hidden

  # See if the Review is shown if enabled
    Given I log in as "teacher1"
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | SC-Question-2  | 1    |
    When I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit settings" in current page administration
    And I set the following fields to these values:
      | id_attemptimmediately | 1 |
      | id_correctnessimmediately | 1 |
      | id_marksimmediately | 1 |
      | id_specificfeedbackimmediately | 1 |
      | id_generalfeedbackimmediately | 1 |
      | id_rightanswerimmediately | 1 |
      | id_overallfeedbackimmediately | 1 |
    And I press "Save and return to course"
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    When I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I should see "1.00/1.00"
    And I should see "100.00 out of 100.00"
    And I should see "Feedback Text 1"
    And I log out

  # See if the Review is shown if disabled
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit settings" in current page administration
    And I click on "Review options" "link"
    And I click on "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_correctnessimmediately']" "css_element"
    And I click on "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_marksimmediately']" "css_element"
    And I click on "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_specificfeedbackimmediately']" "css_element"
    And I click on "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_generalfeedbackimmediately']" "css_element"
    And I click on "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_rightanswerimmediately']" "css_element"
    And I click on "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_overallfeedbackimmediately']" "css_element"
    And I click on "#id_reviewoptionshdr div:contains('Immediately after the attempt') input[id='id_attemptimmediately']" "css_element"
    And I press "Save and return to course"
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Re-attempt quiz"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I should not see "1.00/1.00"
    And I should not see "100.00 out of 100.00"
    And I should not see "Feedback Text 1"
    And I log out

  @javascript
  Scenario: TESTCASE 19 - Part 1.
  # After the Test is submitted control that results (true/false) 
  # selection are correctly aligned to the corresponded option. 
  # Options and results must correspond.
  # Scenario: Shuffling disabled
    
  # Create a response as student
    Given I log in as "student2"
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | SC-Question-3  | 1    |
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I log out

  # Login as a teacher and see if everything works
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"

  # Check Responses Page
    And I navigate to "Responses" in current page administration
    Then "[id='mod-quiz-report-responses-report_r0']" "css_element" should exist
    And I should see "student2@moodle.com"
    And I should see "100.00"

  # Check Review Attempt Page
    And I click on "Review attempt" "link"
    Then "tr:contains('Option Text 1') label[title='This option was been chosen as correct.']" "css_element" should exist
    And I should see "Option Text 1: Correct"
    And I should see "Option Text 2: Not correct"
    And I should see "Option Text 3: Not correct"

  @javascript
  Scenario: TESTCASE 19 - Part 2.
  # After the Test is submitted control that results (true/false) 
  # selection are correctly aligned to the corresponded option. 
  # Options and results must correspond.
  # Scenario: Shuffling enabled
    
  # Create a response as student
    Given I log in as "student2"
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | SC-Question-2  | 1    |
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I log out

  # Login as a teacher and see if everything works
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "Review attempt" "link"
    Then "tr:contains('Option Text 1') label[title='This option was been chosen as correct.']" "css_element" should exist
    And I should see "Option Text 1: Correct"
    And I should see "Option Text 2: Not correct"
    And I should see "Option Text 3: Not correct"

  @javascript
  Scenario: TESTCASE 20.
  # View  results as a teacher.
  # Check "review attempt, "responses", "statistics"
    
  # Create a response as student
    Given I log in as "student2"
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | SC-Question-2  | 1    |
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Finished" 
    And I log out

  # Login as a teacher and see if everything works
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"

  # Check Responses Page
    And I navigate to "Responses" in current page administration
    Then "[id='mod-quiz-report-responses-report_r0']" "css_element" should exist
    And I should see "student2@moodle.com"
    And I should see "100.00"

  # Check Review Attempt Page
    When I click on "Review attempt" "link"
    Then I should see "100.00 out of 100.00"
    Then I should see "Mark 1.00 out of 1.00"
    Then "tr:contains('Option Text 1') label[title='This option was been chosen as correct.']" "css_element" should exist
    And I should see "Option Text 1: Correct"
    And I should see "Option Text 2: Not correct"
    And I should see "Option Text 3: Not correct"

  # Check Responses Page - Delete Entry
    And I navigate to "Responses" in current page administration
    And I click on "#mod-quiz-report-responses-report-selectall-attempts" "css_element"
    And I press "Delete selected attempts"
    And I click on "Yes" "button" in the "Confirmation" "dialogue"
    Then I should not see "student2@moodle.com"