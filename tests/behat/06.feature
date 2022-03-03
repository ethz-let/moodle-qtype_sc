@qtype @qtype_sc @qtype_sc_6
Feature: Step 6

  Background:
    Given the following "users" exist:
      | username  | firstname          | lastname    | email                |
      | teacher   | T1Firstname        | T1Lasname   | teacher@moodle.com   |
      | student1  | S1_SP_100_C        | S1Lastname  | student1@moodle.com  |
      | student9  | S1_SP_100_DIS      | S9Lastname  | student9@moodle.com  |
      | student2  | S2_SP_050          | S2Lastname  | student2@moodle.com  |
      | student3  | S3_SP_000          | S3Lastname  | student3@moodle.com  |
      | student4  | S4_SC10_100_C      | S4Lastname  | student4@moodle.com  |
      | student5  | S5_SC10_000        | S5Lastname  | student5@moodle.com  |
      | student6  | S6_APRIM_100       | S6Lastname  | student6@moodle.com  |
      | student10 | S1_APRIM10_100_DIS | S10Lastname | student10@moodle.com |
      | student7  | S7_APRIM_050       | S7Lastname  | student7@moodle.com  |
      | student8  | S8_APRIM_000       | S8Lastname  | student8@moodle.com  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher   | c1     | editingteacher |
      | student1  | c1     | student        |
      | student2  | c1     | student        |
      | student3  | c1     | student        |
      | student4  | c1     | student        |
      | student5  | c1     | student        |
      | student6  | c1     | student        |
      | student7  | c1     | student        |
      | student8  | c1     | student        |
      | student9  | c1     | student        |
      | student10 | c1     | student        |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype  | name          | template      |
      | Default for c1   | sc     | SC Question 4 | question_four |
    And quiz "Quiz 1" contains the following questions:
      | question          | page |
      | SC Question 4 | 1    |

  @javascript
  Scenario: Testcase 23
  # Test if the Scoring Method information is correctly displayed within quiz attempts

  # The scoring method information should not be disabled by default
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    Then I should not see "Scoring method: Subpoints"
    And I log out

  # Log in as admin and configure the Scoring method to be displayed
    When I log in as "admin"
    And I navigate to "Plugins > Question types > Single Choice (ETH)" in site administration
    And I should see "Administration settings for the single choice (ETH) question type."
    And I set the field "id_s_qtype_sc_showscoringmethod" to "1"
    Then I press "Save changes"
    And I log out

  # The scoring method information should be disabled now
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Continue your attempt"
    Then I should see "Scoring method: Subpoints"
    And I log out

  # Set scoring method to SC1/0
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question SC Question 4" "link" in the "SC Question 4" "list_item"
    And I click on "id_scoringmethod_sconezero" "radio"
    Then I press "id_updatebutton"
    And I log out

  # The scoring method information should be disabled now as SC1/0
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Continue your attempt"
    Then I should see "Scoring method: SC1/0"
    And I log out

  # Set scoring method to Aprime
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question SC Question 4" "link" in the "SC Question 4" "list_item"
    And I click on "id_scoringmethod_aprime" "radio"
    Then I press "id_updatebutton"
    And I log out

  # The scoring method information should be disabled now as Aprime
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Continue your attempt"
    Then I should see "Scoring method: Aprime"
    And I log out

  @javascript
  Scenario: Testcase 9, 10, 11, 20, 23
  # Check grades: Verify that all possible mappings from
  # responses (correct, partially correct, incorrect) to
  # points function as specified for the different scoring
  # methods
  # The correct number of points is awarded, as specified

  # Set Scoring Method to subpoints
    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question SC Question 4" "link" in the "SC Question 4" "list_item"
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_updatebutton"
    And I log out

  # Solving quiz as student1: 100% correct options (SUBPOINTS are activated) Using checkbox
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student9: 100% correct options (SUBPOINTS are activated) Using distractors
    When I log in as "student9"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student2: 50% correct options (SUBPOINTS are activated)
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student3: 0% correct options (SUBPOINTS are activated)
    When I log in as "student3"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 2') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Check results for Subpoints
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"

    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student1@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And "//td[contains(.,'Option Text 1')]/input[@checked='checked']" "xpath_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student9@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And "//tr[contains(.,'Option Text 2')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 3')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 4')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 5')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student2@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Partially correct')" "css_element" should exist
    And ".grade:contains('Mark 0.50 out of 1.00')" "css_element" should exist
    And "//tr[contains(.,'Option Text 3')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 4')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 5')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student3@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Incorrect')" "css_element" should exist
    And ".grade:contains('Mark 0.00 out of 1.00')" "css_element" should exist
    And "//td[contains(.,'Option Text 2')]/input[@checked='checked']" "xpath_element" should exist

  # Set Scoring Method to SC 1/0
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question SC Question 4" "link" in the "SC Question 4" "list_item"
    And I click on "id_scoringmethod_sconezero" "radio"
    And I press "id_submitbutton"
    And I log out

  # Solving quiz as student4: 100% correct options (SC/10 is activated) - Using checkbox
    When I log in as "student4"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student5: 0% correct options (SC1/0 is activated)
    When I log in as "student5"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 2') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Check results for SC1/0
    When I log in as "teacher"
    And I am on "Course 1" course homepage

    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student4@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And "//td[contains(.,'Option Text 1')]/input[@class='optionradio' and @checked='checked']" "xpath_element" should exist

    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student5@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Incorrect')" "css_element" should exist
    And ".grade:contains('Mark 0.00 out of 1.00')" "css_element" should exist
    And "//td[contains(.,'Option Text 2')]/input[@class='optionradio' and @checked='checked']" "xpath_element" should exist

  # Set Scoring Method to Aprime
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question SC Question 4" "link" in the "SC Question 4" "list_item"
    And I click on "id_scoringmethod_aprime" "radio"
    And I press "id_submitbutton"
    And I log out

  # Solving quiz as student6: 100% correct options (Aprime is activated) - using checkbox
    When I log in as "student6"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student10: 100% correct options (Aprime is activated) - using distractors
    When I log in as "student10"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student7: 1 false option -> 50% (Aprime is activated)
    When I log in as "student7"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student8: 2 false option -> 0% (Aprime is activated)
    When I log in as "student8"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('Option Text 4') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "tr:contains('Option Text 5') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

# Check results for Aprime
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student6@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And "//td[contains(.,'Option Text 1')]/input[@class='optionradio' and @checked='checked']" "xpath_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student10@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And "//tr[contains(.,'Option Text 2')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 3')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 4')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 5')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student7@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Partially correct')" "css_element" should exist
    And ".grade:contains('Mark 0.50 out of 1.00')" "css_element" should exist
    And "//tr[contains(.,'Option Text 3')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 4')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 5')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student8@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Incorrect')" "css_element" should exist
    And ".grade:contains('Mark 0.00 out of 1.00')" "css_element" should exist
    And "//tr[contains(.,'Option Text 4')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//tr[contains(.,'Option Text 5')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
