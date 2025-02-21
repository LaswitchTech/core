<?php

/**
 * Core Framework - User
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Backends;
use LaswitchTech\Core\Objects;
use Exception;

class User {

    // Global Properties
    protected $Database;

    // Properties
    protected $user;
    protected $backend;
    protected $roles;
    protected $groups;

    /**
     * Constructor
     *
     * @param int|string $user
     * @throws Exception
     */
    public function __construct(int|string $user)
    {
        // Import Global Variables
        global $DATABASE;

        // Initialize Properties
        $this->Database = $DATABASE;

        // Retrieve User
        $query = $this->Database->query();
        $user = $query->table('users')
            ->select('*')
            ->join('backend', 'backends', 'id')
            ->join('session', 'sessions', 'id')
            ->join('vcard', 'vcards', 'id')
            ->join('organization', 'organizations', 'id')
            ->join('pin', 'pins', 'id')
            ->join('token', 'tokens', 'id')
            ->where('id', $user, '=', 'OR')
            ->where('username', $user, '=', 'OR')
            ->limit(1)
            ->result();

        // Check if user exists
        if (count($user) == 0) {
            throw new Exception('User not found');
        }

        // Set Properties
        $this->user = $user[0];

        // Initialize Backend
        switch($this->user['backend']['type']) {
            case 'local':
                $this->backend = new Backends\Local($this->user['backend']['id'],$this->user['backend']);
                break;
            // case 'ldap':
            //     $this->backend = new Backends\LDAP($this->user['backend']['id'],$this->user['backend']);
            //     break;
            // case 'addc':
            //     $this->backend = new Backends\ADDC($this->user['backend']['id'],$this->user['backend']);
            //     break;
            // case 'smtp':
            //     $this->backend = new Backends\SMTP($this->user['backend']['id'],$this->user['backend']);
            //     break;
            // case 'imap':
            //     $this->backend = new Backends\IMAP($this->user['backend']['id'],$this->user['backend']);
            //     break;
            // case 'oauth':
            //     $this->backend = new Backends\OAuth($this->user['backend']['id'],$this->user['backend']);
            //     break;
            default:
                throw new Exception('Backend not supported');
        }

        // Retrieve User's Groups
        $query = $this->Database->query();
        $query->table('groups')
            ->select('*')
            ->index('name')
            ->filter()
            ->where('id', 9999, '<>')
            ->filter()
            ->where('users', $this->user['id'], 'CONTAINS', 'OR')
            ->where('organizations', $this->user['organization']['id'], 'CONTAINS', 'OR')
            ->where('isDefault', 1, '=', 'OR');

        // Retrieve Groups
        $this->groups = $query->result();

        // Retrieve User's Roles
        $query = $this->Database->query();
        $query->table('roles')
            ->select('*')
            ->index('name')
            ->filter()
            ->where('id', 9999, '<>')
            ->filter()
            ->where('users', $this->user['id'], 'CONTAINS', 'OR')
            ->where('organizations', $this->user['organization']['id'], 'CONTAINS', 'OR')
            ->where('isDefault', 1, '=', 'OR');

        // Filter by Groups
        foreach($this->groups as $group){
            $query->where('groups', $group['id'], 'CONTAINS', 'OR');
        }

        // Retrieve Groups
        $this->roles = $query->result();
    }

    /**
     * Retrieve the Backend
     *
     * @return Backend
     */
    public function backend()
    {
        return $this->backend;
    }

    /**
     * Retrieve the User ID
     *
     * @return int
     */
    public function id(): int
    {
        return $this->user['id'];
    }

    /**
     * Check if the User is Verified
     *
     * @return bool
     */
    public function verified(): bool
    {
        return boolval($this->user['isVerified']);
    }

    /**
     * Check if the User is Banned
     *
     * @return bool
     */
    public function banned(): bool
    {
        return boolval($this->user['isBanned']);
    }

    /**
     * Check if the User is Deleted
     *
     * @return bool
     */
    public function deleted(): bool
    {
        return boolval($this->user['isDeleted']);
    }

    /**
     * Create a new Session object
     *
     * @return Objects\Session
     */
    public function session(): Objects\Session
    {
        return new Objects\Session($this);
    }

    // /**
    //  * Create a new Organization object
    //  *
    //  * @return Objects\Organization
    //  */
    // public function organization(): Objects\Organization
    // {
    //     return new Objects\Organization();
    // }

    // /**
    //  * Create a new Pin object
    //  *
    //  * @return Objects\Pin
    //  */
    // public function pin(): Objects\Pin
    // {
    //     return new Objects\Pin();
    // }

    // /**
    //  * Create a new Profile object
    //  *
    //  * @return Objects\Profile
    //  */
    // public function profile(): Objects\Profile
    // {
    //     return new Objects\Profile();
    // }

    // /**
    //  * Create a new Token object
    //  *
    //  * @return Objects\Token
    //  */
    // public function token(): Objects\Token
    // {
    //     return new Objects\Token();
    // }
}
