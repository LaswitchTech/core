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

        // Retrieve the organization's vCard
        $query = $this->Database->query()
            ->table('vcards')
            ->select('*')
            ->where('id', 9999, '<>')
            ->where('id', $this->user['organization']['vcard'])
            ->limit(1);
        $this->user['organization']['vcard'] = $query->fetch()[0];

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
            ->where('isDefault', 1, '=', 'OR');

        // Retrieve Groups
        $groups = $query->result();

        // Initialize Groups
        $this->groups = [];
        foreach($groups as $group){
            $this->groups[$group['name']] = new Objects\Group($group['name'],$group);
        }

        // Retrieve User's Roles
        $query = $this->Database->query();
        $query->table('roles')
            ->select('*')
            ->index('name')
            ->filter()
            ->where('id', 9999, '<>')
            ->filter()
            ->where('users', $this->user['id'], 'CONTAINS', 'OR')
            ->where('isDefault', 1, '=', 'OR');

        // Filter by Groups
        foreach($groups as $group){
            $query->where('groups', $group['id'], 'CONTAINS', 'OR');
        }

        // Retrieve Results
        $roles = $query->result();

        // Initialize Roles
        $this->roles = [];
        foreach($roles as $role){
            $this->roles[$role['name']] = new Objects\Role($role['name'],$role);
        }
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
     * Magic Method to catch all undefined properties
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->user[$key] ?? null;
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
     * Retrieve the User Username
     *
     * @return string
     */
    public function username(): string
    {
        return $this->user['username'];
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

    /**
     * Retrieve the User's Groups
     *
     * @param bool $asObjects
     * @return array
     */
    public function groups(bool $asObjects = false): array
    {
        if($asObjects){
            return $this->groups;
        }
        return array_keys($this->groups);
    }

    /**
     * Retrieve a Group
     *
     * @param string $name
     * @return Objects\Group
     */
    public function group(string $name): ?Objects\Group
    {
        return $this->groups[$name] ?? null;
    }

    /**
     * Retrieve the User's Roles
     *
     * @param bool $asObjects
     * @return array
     */
    public function roles(bool $asObjects = false): array
    {
        if($asObjects){
            return $this->roles;
        }
        return array_keys($this->roles);
    }

    /**
     * Retrieve a Role
     *
     * @param string $name
     * @return Objects\Role
     */
    public function role(string $name): ?Objects\Role
    {
        return $this->roles[$name] ?? null;
    }

    /**
     * Retrieve the User's vCard
     *
     * @param string $key
     * @return mixed
     */
    public function vcard(string $key = null): mixed
    {
        if($key){
            return $this->user['vcard'][$key] ?? null;
        }
        return $this->user['vcard'];
    }

    /**
     * Retrieve the User's Associates
     */
    public function associates(): array
    {
        $users = [];
        foreach($this->roles as $key => $role){
            foreach($role->members('users') as $member){
                $users[$member['id']] = $member;
            }
        }
        return $users;
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
