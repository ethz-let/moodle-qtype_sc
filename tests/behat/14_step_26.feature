@qtype @qtype_sc @qtype_sc_step_26
Feature: Step 26

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

 @javascript
  Scenario: TESTCASE 26 - Part 1: Export.
  # Export and import SC questions from question bank.
  # Images etc. should also be backuped and restored.

  # Upload images
    And I output "[SC - TESTCASE 26 - Part 1- begin]"
    Given the following "question categories" exist:
      | contextlevel         | reference      | name                 |
      | Course               | c1             | Default for c1       |
    And the following "questions" exist:
      | questioncategory     | qtype          | name                 | template        |
      | Default for c1       | sc             | SC-Question-001      | question_one    |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"
    And I click on "Edit" "link" in the "SC-Question-001" "table_row"

  # Add image to question stem
    And I click on "Insert or edit image" "button" in the "#id_generalheader" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://127.0.0.1/question/type/sc/tests/media/testimage1.png"
    And I press "Download"
    And I click on "testimage1.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage1AltDescription"
    And I click on "Save image" "button"
    And I press "Save changes and continue editing"

  # Add image to optiontext
    And I click on "Insert or edit image" "button" in the ".optiontext" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://127.0.0.1/question/type/sc/tests/media/testimage2.png"
    And I press "Download"
    And I click on "testimage2.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage2AltDescription"
    And I click on "Save image" "button"
    And I press "id_submitbutton"

  # Export a SC Question
    And I click on "Export" "link"
    And I set the field "id_format_xml" to "1"
    And I press "Export questions to file"
    Then following "click here" should download between "6000" and "8000" bytes
    And I click on css ".usermenu"
    And I click on "Log out" "link"
    And I output "[SC - TESTCASE 26 - Part 1- end]"

 @javascript
  Scenario: TESTCASE 26 - Part 1: Export.
  # Import
    And I output "[SC - TESTCASE 26 - Part 2- begin]"
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"
    And I click on "Import" "link"
    And I set the field "id_format_xml" to "1"
    And I click on "Choose a file..." "button" in the "#id_importfileupload" "css_element"
    And I click on "Upload a file" "link" in the ".fp-repo-area" "css_element"
    And I attach file "/var/www/moodle/question/type/sc/tests/media/testquestion.moodle.xml" to "input[name='repo_upload_file']"
    And I press "Upload this file"
    And I click on css "#id_importfileupload input[name='submitbutton']"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I press "Continue"

  # Check
    And I should see "SC-Question-001"
    And I click on "Preview" "link" in the "SC-Question-001" "table_row"
    And I switch to "questionpreview" window
    Then element with xpath "[alt='testimage1AltDescription']" should exist
    And I should not see "testimage1AltDescription"
    And element with xpath "[alt='testimage2AltDescription']" should exist
    And I should not see "testimage2AltDescription"
    And I should see "Option Text 1"
    And I should see "Option Text 2"
    When I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on css "tr:contains('Option Text 1') label[title='Click to choose as correct option.']"
    And I press "Check"
    Then I should see "Feedback Text 1"
    And I should see "Option Text 1: Correct"
    And I should see "Option Text 2: Not correct"
    And I output "[SC - TESTCASE 26 - Part 2- end]"
