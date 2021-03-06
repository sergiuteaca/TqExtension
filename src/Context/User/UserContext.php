<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\User;

// Helpers.
use Behat\Gherkin\Node\TableNode;

class UserContext extends RawUserContext
{
    /**
     * @example
     * Then I am logged in as a user with "CRM Client" role and filled fields
     *   | Full name                | Sergii Bondarenko |
     *   | Position                 | Developer         |
     *   | field_crm_user_company   | FFW               |
     *
     * @param string $roles
     *   User roles, separated by comma.
     * @param TableNode $fields
     *   | Field machine name or label | Value |
     *
     * @throws \EntityMetadataWrapperException
     *   When user object cannot be saved.
     * @throws \Exception
     *   When required fields are not filled.
     *
     * @Given /^(?:|I am )logged in as a user with "([^"]*)" role(?:|s)(?:| and filled fields:)$/
     */
    public function loginCreatedUser($roles, TableNode $fields = null)
    {
        $this->createDrupalUser($roles, $fields);
        $this->loginUser();
    }

    /**
     * @see loginCreatedUser()
     *
     * @Then /^(?:|I )create a user with "([^"]*)" role(?:|s)(?:| and filled fields:)$/
     */
    public function createDrupalUser($roles, TableNode $fields = null)
    {
        $this->createUserWithRoles($roles, null !== $fields ? $fields->getRowsHash() : []);
    }

    /**
     * @param TableNode $credentials
     *   | username | BR0kEN |
     *   | password | p4sswd |
     *
     * @throws \Exception
     *   When user cannot be authorized.
     *
     * @Given /^(?:|I )am logged in with credentials:/
     */
    public function loginWithCredentials(TableNode $credentials)
    {
        $this->fillLoginForm($credentials->getRowsHash());
    }

    /**
     * This step must be used instead of "I am an anonymous user" because it
     * has more strict checking for authenticated user.
     *
     * @Given /^I am unauthorized user$/
     * @Given /^I am log out$/
     */
    public function logoutDrupalUser()
    {
        $this->logoutUser();
    }

    /**
     * @param $operation
     *   The operation applied on the user: view or edit.
     * @param $column
     *   The column in users table into which the value will be searched.
     * @param $value
     *   The value to search in users table.
     *
     * @throws \Exception
     *   When user not found.
     *
     * @When /^I (visit|view|edit) user with (name|mail) "([^"]+)"$/
     */
    public function visitUser($operation, $column, $value)
    {
        if ('visit' === $operation) {
            $operation = 'view';
        }

        $uid = $this->getUserByColumn($column, $value);

        $this->getRedirectContext()->visitPage("user/$uid/$operation");
    }

    /**
     * @AfterScenario
     */
    public function afterUserScenario() {
        // Logout, when scenario finished an execution, is required for "Scenario Outline" because an
        // object will not be instantiated for every iteration and user data, from previous one, will
        // be kept.
        $this->logoutUser();
    }
}
