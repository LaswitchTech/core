<?php

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Model;

class coreModel extends Model {

    // Properties
    private $Table;

    /**
     * Get the table name
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->Table;
    }

    /**
     * Create a new record and return the id
     *
     * @param string $table
     * @param array $data
     * @return int
     */
    public function create(string $table, array $data): int
    {
        // Set the table
        $this->Table = $table;

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->Table)
            ->insert($data);

        // Execute the Query
        $affectedRows = $Query->execute();

        // Execute the Query
        return $Query->lastId();
    }

    /**
     * Read a record(s)
     *
     * @param string $table
     * @param int $id
     * @return array
     */
    public function read(string $table, ?int $id = null): array
    {
        // Set the table
        $this->Table = $table;

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->Table)
            ->select('*');

        // Check if id is provided
        if($id){
            $Query->where('id', $id)->limit(1);
        }

        // Execute the Query
        return $Query->result();
    }

    /**
     * Update a record
     *
     * @param string $table
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update(string $table, int $id, array $data): int
    {
        // Set the table
        $this->Table = $table;

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->Table)
            ->update($data)
            ->where('id', $id);

        // Execute the Query
        return $Query->execute();
    }

    /**
     * Delete a record
     *
     * @param string $table
     * @param int $id
     * @return int
     */
    public function delete(string $table, int $id): int
    {
        // Set the table
        $this->Table = $table;

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->Table)
            ->delete()
            ->where('id', $id);

        // Execute the Query
        return $Query->execute();
    }
}
