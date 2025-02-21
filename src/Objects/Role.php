<?php

/**
 * Core Framework - Role
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;

class Role {

    // Global Properties
    protected $Database;

    // Properties
    private $name;
    private $description;
    private $organizations = [];
    private $users = [];
    private $groups = [];
    private $permissions = [];
    private $default; // Applies to all users

    /**
     * Constructor
     */
    public function __construct(string $name)
    {
        // Import Global Variables
        global $DATABASE;

        // Initialize Properties
        $this->Database = $DATABASE;

        // Retrieve Role
        $query = $this->Database->query();
        $role = $query->table('roles')
            ->select('name, description, organizations, users, groups, permissions, isDefault')
            ->where('name', $name)
            ->limit(1)
            ->result();

        // Set Properties
        $this->name = count($role) > 0 ? $role[0]['name'] : $name;
        $this->description = count($role) > 0 ? $role[0]['description'] : null;
        $this->organizations = count($role) > 0 ? $role[0]['organizations'] : null;
        $this->users = count($role) > 0 ? $role[0]['users'] : null;
        $this->groups = count($role) > 0 ? $role[0]['groups'] : null;
        $this->permissions = count($role) > 0 ? $role[0]['permissions'] : null;
        $this->default = count($role) > 0 ? $role[0]['isDefault'] : null;

        // Decode Members
        $this->organizations = json_decode($this->organizations ?? '', true) ?? [];
        $this->users = json_decode($this->users ?? '', true) ?? [];
        $this->groups = json_decode($this->groups ?? '', true) ?? [];
        $this->permissions = json_decode($this->permissions ?? '', true) ?? [];
    }

    /**
     * Set the Role Description
     *
     * @param string $description
     * @return self
     */
    public function describe(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Add a Member from the Role
     *
     * @param string $type
     * @param int $id
     * @return self
     */
    public function add(string $type, int $id): self
    {
        switch($type){
            case 'organizations':
                $this->organizations[] = $id;
                $this->organizations = array_unique($this->organizations);
                sort($this->organizations);
                break;
            case 'users':
                $this->users[] = $id;
                $this->users = array_unique($this->users);
                sort($this->users);
                break;
            case 'groups':
                $this->groups[] = $id;
                $this->groups = array_unique($this->groups);
                sort($this->groups);
                break;
        }
        return $this;
    }

    /**
     * Remove a Member from the Role
     *
     * @param string $type
     * @param int $id
     * @return self
     */
    public function remove(string $type, int $id): self
    {
        switch($type){
            case 'organizations':
                $this->organizations = array_diff($this->organizations, [$id]);
                sort($this->organizations);
                break;
            case 'users':
                $this->users = array_diff($this->users, [$id]);
                sort($this->users);
                break;
            case 'groups':
                $this->groups = array_diff($this->groups, [$id]);
                sort($this->groups);
                break;
        }
        return $this;
    }

    /**
     * Set Permission Level
     *
     * @param string $permission
     * @param int $level
     * @return self
     */
    public function set(string $permission, int $level): self
    {
        $this->permissions[$permission] = $level;
        sort($this->permissions);
        return $this;
    }

    /**
     * Unset Permission Level
     *
     * @param string $permission
     * @return self
     */
    public function unset(string $permission): self
    {
        unset($this->permissions[$permission]);
        sort($this->permissions);
        return $this;
    }

    /**
     * Retrieve Members from the Role
     *
     * @param string $type
     * @return self
     */
    public function members(string $type): array
    {
        switch($type){
            case 'organizations':
                return $this->organizations;
            case 'users':
                return $this->users;
            case 'groups':
                return $this->groups;
        }
        return [];
    }

    /**
     * Retrieve Permissions from the Role
     *
     * @return self
     */
    public function permissions(): array
    {
        return $this->permissions;
    }

    /**
     * Set Role as Default
     *
     * @param bool $default
     * @return self
     */
    public function default(bool $default = true): self
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Save the Role
     *
     * @return self
     */
    public function save(): self
    {
        // Create the Query
        $query = $this->Database->query();

        // Check if the Role exists
        $role = $query->table('roles')
            ->select('id')
            ->where('name', $this->name)
            ->limit(1)
            ->result();

        // Create a new Query
        $query = $this->Database->query();

        // Check if the Role exists
        if(count($role) > 0){

            // Update the Role
            $query->table('roles')
                ->update([
                    'description' => $this->description,
                    'organizations' => json_encode($this->organizations, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'users' => json_encode($this->users, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'groups' => json_encode($this->groups, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'permissions' => json_encode($this->permissions, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'isDefault' => $this->default
                ])
                ->where('name', $this->name)
                ->result();
        } else {

            // Create the Role
            $query->table('roles')
                ->insert([
                    'name' => $this->name,
                    'description' => $this->description,
                    'organizations' => json_encode($this->organizations, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'users' => json_encode($this->users, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'groups' => json_encode($this->groups, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'permissions' => json_encode($this->permissions, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'isDefault' => $this->default
                ])
                ->result();
        }

        return $this;
    }
}
