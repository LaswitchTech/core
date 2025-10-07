<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;

class Organization {

    // Global Properties
    protected $Database;

    // Properties
    private $organization;
    private $vcard;
    private $users;

    /**
     * Constructor
     */
    public function __construct(int $id)
    {
        // Import Global Variables
        global $DATABASE;

        // Initialize Properties
        $this->Database = $DATABASE;

        // Retrieve Organization
        $query = $this->Database->query();
        $organization = $query->table('organizations')
            ->select('*')
            ->join('vcard', 'vcards', 'id')
            ->where('id', $id)
            ->limit(1)
            ->fetch();

        // Set Properties
        $this->organization = $organization[0] ?? [];

        // Retrieve the vcards
        $query = $this->Database->query();
        $vcard = $query->table('vcards')
            ->select('*')
            ->join('avatar', 'files', 'id')
            ->where('id', $this->organization['vcard']['id'])
            ->limit(1)
            ->fetch();

        // Set Properties
        $this->vcard = $vcard[0] ?? [];

        // Decode Members
        $users = json_decode($this->organization['users'] ?? '', true) ?? [];

        // Retrieve the users
        foreach($users as $key => $userId){
            $this->add($userId, true);
        }
    }

    /**
     * Retrieve the Organization's Details
     *
     * @param string $key
     * @return mixed
     */
    public function __get(?string $key = null): mixed
    {
        if($key){
            return $this->organization[$key] ?? ($this->vcard[$key] ?? ($this->organization['vcard'][$key] ?? null));
        }
        return $this->organization;
    }

    /**
     * Retrieve the Organization's vCard
     *
     * @return array
     */
    public function vcard(): array
    {
        return $this->organization['vcard'];
    }

    /**
     * Add a Member from the Organization
     *
     * @param int $id
     * @param bool $dry
     * @return self
     */
    public function add(int $id, bool $dry = false): self
    {
        if(!isset($this->users[$id])){

            // Create a new Query
            $Query = $this->Database->query()
                ->table('users')
                ->select('*')
                ->join('vcard', 'vcards', 'id')
                ->where('id', $id)
                ->limit(1);

            // Retrieve the user
            $user = $Query->fetch();

            // Check if the user exists
            if(count($user) > 0){

                // Add the user to the list
                $this->users[$user[0]['id']] = $user[0];

                // Check if we are in dry mode
                if($dry){
                    return $this;
                }

                // Create a new Query
                $Query = $this->Database->query()
                    ->table('organizations')
                    ->update(['users' => json_encode(array_keys($this->users), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)])
                    ->where('id', $this->organization['id']);

                // Execute the Query
                $Query->execute();
            }
        }
        return $this;
    }

    /**
     * Remove a Member from the Organization
     *
     * @param int $id
     * @return self
     */
    public function remove(int $id): self
    {
        if(isset($this->users[$id])){

            // Remove the user from the list
            unset($this->users[$id]);

            // Create a new Query
            $Query = $this->Database->query()
                ->table('organizations')
                ->update(['users' => json_encode(array_keys($this->users), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)])
                ->where('id', $this->organization['id']);

            // Execute the Query
            $Query->execute();
        }
        return $this;
    }

    /**
     * Retrieve Members from the Organization
     *
     * @param string $type
     * @return self
     */
    public function members(): array
    {
        return $this->users;
    }
}
