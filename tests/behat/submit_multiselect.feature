@mod @mod_surveypro
Feature: make a submission test for each available item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a multiselect item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for each available item
    Given the following "courses" exist:
      | fullname                             | shortname       | category |
      | Test submission for multiselect item | Submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Submission test | editingteacher |
      | student1 | Submission test | student        |

    And I log in as "teacher1"
    And I follow "Test submission for multiselect item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                                             |
      | Description | This is a surveypro to test submission of multiselect item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Multiple selection"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                | What do you usually eat for breakfast? |
      | Indent                 | 0                                      |
      | Question position      | left                                   |
      | Element number         | 10                                     |
    And I fill the textarea "Options" with multiline content "milk\nsugar\njam\nchocolate"
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Test submission for multiselect item"
    And I follow "Surveypro test"
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_multiselect_1 | jam |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions displayed