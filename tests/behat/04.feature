@qtype @qtype_sc @qtype_sc_4
Feature: Step 4

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
      | questioncategory | qtype | name          | template      |
      | Default for c1   | sc    | SC-Question-2 | question_four |
      | Default for c1   | sc    | SC-Question-3 | question_four |
      | Default for c1   | sc    | SC-Question-4 | question_four |
    And quiz "Quiz 1" contains the following questions:
      | question      | page |
      | SC-Question-2 | 1    |
      | SC-Question-3 | 2    |
      | SC-Question-4 | 3    |

  @javascript
  Scenario: Testcase 18
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Settings" in current page administration
    And I click on "Layout" "link"
    And I set the field "New page" to "Never, all questions on one page"
    And I press "Save and display"
    And I press "Preview quiz"
    When I click on "[id^='question'][id$='-1'] tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I click on "[id^='question'][id$='-1'] tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "[id^='question'][id$='-2'] tr:contains('Option Text 2') label[title='Click to choose as correct option.']" "css_element"
    And I click on "[id^='question'][id$='-2'] tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "[id^='question'][id$='-3'] tr:contains('Option Text 3') label[title='Click to choose as correct option.']" "css_element"
    And I click on "[id^='question'][id$='-3'] tr:contains('Option Text 1') label[title='Click to cross out as incorrect option.']" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'Option Text 1')]//input[@class='optionradio' and @checked='checked']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'Option Text 2')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-2']//tr[contains(.,'Option Text 2')]//input[@class='optionradio' and @checked='checked']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-2']//tr[contains(.,'Option Text 3')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-3']//tr[contains(.,'Option Text 3')]//input[@class='optionradio' and @checked='checked']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-3']//tr[contains(.,'Option Text 1')]/td/input[@class='distractorcheckbox' and @checked='checked']" "xpath_element" should exist
    And I log out

  @javascript
  Scenario: Testcase 19
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Settings" in current page administration
    And I click on "Layout" "link"
    And I set the field "New page" to "Never, all questions on one page"
    And I press "Save and display"
    And I press "Preview quiz"
  # Quesion 1: Option and distractor chosen
    When I click on "[id^='question'][id$='-1'] tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I click on "[id^='question'][id$='-1'] tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']" "css_element"
  # Quesion 2: Distractor chosen
    And I click on "[id^='question'][id$='-2'] tr:contains('Option Text 3') label[title='Click to cross out as incorrect option.']" "css_element"
  # Quesion 3: Nothing chosen
    And I press "Finish attempt ..."
    Then "//tr[starts-with(@class,'quizsummary1')]//td[contains(.,'Answer saved')]" "xpath_element" should exist
    And "//tr[starts-with(@class,'quizsummary2')]//td[contains(.,'Incomplete answer')]" "xpath_element" should exist
    And "//tr[starts-with(@class,'quizsummary3')]//td[contains(.,'Not yet answered')]" "xpath_element" should exist
    And I log out

  @javascript
  Scenario: Testcase 16

  # (12) Navigation and label
  # Login as teacher and set Question behavior to "Deferred feedback"
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Settings" in current page administration
    And I click on "Question behaviour" "link"
    And I set the field "How questions behave" to "Deferred feedback"
    And I press "Save and return to course"
    And I log out

  # Login as student and see if everything works
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    When I press "Attempt quiz"

  # No option selected
    When I click on "quiznavbutton2" "link"
    Then "#quiznavbutton1[title='Not yet answered']" "css_element" should exist

  #One option selected
    When I click on "tr:contains('Option Text 1') label[title='Click to choose as correct option.']" "css_element"
    And I click on "quiznavbutton1" "link"
    Then "#quiznavbutton2[title='Answer saved']" "css_element" should exist

  #One distractor selected
    When I click on "quiznavbutton3" "link"
    And I click on "tr:contains('Option Text 2') label[title='Click to cross out as incorrect option.']" "css_element"
    And I click on "quiznavbutton1" "link"
    Then "#quiznavbutton3[title='Incomplete answer']" "css_element" should exist
