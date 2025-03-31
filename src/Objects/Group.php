<?php

/**
 * Core Framework - Group
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;

class Group {

    // Global Properties
    protected $Database;

    // Properties
    private $name;
    private $description;
    private $users;
    private $default;

    /**
     * Constructor
     */
    public function __construct(string $name)
    {
        // Import Global Variables
        global $DATABASE;

        // Initialize Properties
        $this->Database = $DATABASE;

        // Retrieve Group
        $query = $this->Database->query();
        $group = $query->table('groups')
            ->select('*')
            ->where('name', $name)
            ->limit(1)
            ->result();

        // Set Properties
        $this->name = count($group) > 0 ? $group[0]['name'] : $name;
        $this->description = count($group) > 0 ? $group[0]['description'] : null;
        $this->users = count($group) > 0 ? $group[0]['users'] : null;
        $this->default = count($group) > 0 ? $group[0]['isDefault'] : null;

        // Decode Members
        $this->users = json_decode($this->users ?? '', true) ?? [];
    }

    /**
     * Set the Group Description
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
     * Add a Member from the Group
     *
     * @param int $id
     * @return self
     */
    public function add(int $id): self
    {
        switch($type){
            case 'users':
                $this->users[] = $id;
                $this->users = array_unique($this->users);
                sort($this->users);
                break;
        }
        return $this;
    }

    /**
     * Remove a Member from the Group
     *
     * @param int $id
     * @return self
     */
    public function remove(int $id): self
    {
        switch($type){
            case 'users':
                $this->users = array_diff($this->users, [$id]);
                sort($this->users);
                break;
        }
        return $this;
    }

    /**
     * Retrieve Members from the Group
     *
     * @param string $type
     * @return self
     */
    public function members(string $type): array
    {
        switch($type){
            case 'users':
                return $this->users;
        }
        return [];
    }

    /**
     * Set Group as Default
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
     * Save the Group
     *
     * @return self
     */
    public function save(): self
    {
        // Create the Query
        $query = $this->Database->query();

        // Check if the Group exists
        $group = $query->table('groups')
            ->select('id')
            ->where('name', $this->name)
            ->where('id', 9999, '<>')
            ->limit(1)
            ->result();

        // Create a new Query
        $query = $this->Database->query();

        // Check if the Group exists
        if(count($group) > 0){

            // Update the Group
            $query->table('groups')
                ->update([
                    'description' => $this->description,
                    'users' => json_encode($this->users, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'isDefault' => $this->default
                ])
                ->where('name', $this->name)
                ->result();
        } else {

            // Create the Group
            $query->table('groups')
                ->insert([
                    'name' => $this->name,
                    'description' => $this->description,
                    'users' => json_encode($this->users, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    'isDefault' => $this->default
                ])
                ->result();
        }

        return $this;
    }
}
