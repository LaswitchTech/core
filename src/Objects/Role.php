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
    private $users = [];
    private $groups = [];
    private $permissions = [];
    private $default; // Applies to all users

    /**
     * Constructor
     *
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data = [])
    {
        // Import Global Variables
        global $DATABASE;

        // Initialize Properties
        $this->Database = $DATABASE;

        // Check if Data is provided
        if(!empty($data)){

            // Set Properties
            $this->name = $name;
            $this->description = $data['description'] ?? null;
            $this->users = $data['users'] ?? [];
            $this->groups = $data['groups'] ?? [];
            $this->permissions = $data['permissions'] ?? [];
            $this->default = $data['isDefault'] ?? null;
        } else {

            // Retrieve Role
            $query = $this->Database->query();
            $role = $query->table('roles')
                ->select('*')
                ->where('name', $name)
                ->limit(1)
                ->result();

            // Set Properties
            $this->name = count($role) > 0 ? $role[0]['name'] : $name;
            $this->description = count($role) > 0 ? $role[0]['description'] : null;
            $this->users = count($role) > 0 ? ($role[0]['users'] ?? '[]') : '[]';
            $this->groups = count($role) > 0 ? ($role[0]['groups'] ?? '[]') : '[]';
            $this->permissions = count($role) > 0 ? ($role[0]['permissions'] ?? '[]') : '[]';
            $this->default = count($role) > 0 ? $role[0]['isDefault'] : null;
        }

        // Decoding JSON
        $this->users = (gettype($this->users) == "string") ? (json_decode($this->users ?? '', true) ?? []) : $this->users;
        $this->groups = (gettype($this->groups) == "string") ? (json_decode($this->groups ?? '', true) ?? []) : $this->groups;
        $this->permissions = (gettype($this->permissions) == "string") ? (json_decode($this->permissions ?? '', true) ?? []) : $this->permissions;

        // Retrieve Members
        foreach(['groups', 'users'] as $type){
            foreach($this->{$type} as $key => $id){
                $query = $this->Database->query();
                $member = $query->table($type)
                    ->select('*')
                    ->where('id', $id)
                    ->limit(1)
                    ->index('id')
                    ->fetch();
                if(count($member) > 0){
                    $this->{$type}[$id] = $member[array_key_first($member)];
                    if($type != 'users'){
                        $this->{$type}[$id]['users'] = json_decode($this->{$type}[$id]['users'] ?? '[]', true) ?? [];
                        $this->users = array_unique(array_merge($this->users, $this->{$type}[$id]['users']));
                    }
                }
                unset($this->{$type}[$key]);
            }
        }
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
     * @return array
     */
    public function members(string $type): array
    {
        switch($type){
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
     * @param string $key
     * @return self
     */
    public function permissions(?string $key = null): mixed
    {
        if($key){
            return $this->permissions[$key] ?? 0;
        }
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
            ->where('id', 9999, '<>')
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
