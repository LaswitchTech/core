<?php

// Declaring namespace
namespace LaswitchTech\Core\Base;

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Endpoint;
use Exception;

abstract class BaseEndpoint extends Endpoint {

    // Properties
    protected $basename;
    protected $name;
    protected $required = [];
    protected $optional = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call Parent Constructor
        parent::__construct();
    }

    /**
     * Initialize the Endpoint
     *
     * @param string $table
     * @param string|null $primary
     * @return void
     */
    protected function init(string $basename, bool $public = false): void
    {
        // Set the table name
        $this->basename = $basename;
        $this->name = ucfirst($basename);

        // Retrieve the namespace
        $namespace = $this->Request->getNamespace();

        // Set Global access
        $this->Public = $public;
        $this->Level = 0;

        // Check if the endpoint is public
        if(!$this->Public){

            // Set Level
            switch($namespace){
                case "/".$this->basename."/count":
                case "/".$this->basename."/fetch":
                case "/".$this->basename."/fetchAll":
                case "/".$this->basename."/describe":
                    $this->Level = 1;
                    break;
                case "/".$this->basename."/create":
                    $this->Level = 2;
                    break;
                case "/".$this->basename."/update":
                    $this->Level = 3;
                    break;
                case "/".$this->basename."/delete":
                case "/".$this->basename."/archive":
                case "/".$this->basename."/recover":
                    $this->Level = 4;
                    break;
            }
        }
    }

    /**
     * Count the records
     */
    public function countAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the conditions
        $conditions = $this->Request->getParams('REQUEST','conditions') ?? [];

        // Retrieve the conjunction
        $conjunction = $this->Request->getParams('REQUEST','conjunction') ?? 'AND';

        // Check if the records is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "POST"){

                // Retrieve the services records
                $message['data']['count'] = $this->Model->{$this->name}->count($conditions, $conjunction);
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Retrieve multiple records
     */
    public function fetchAllAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the conditions
        $conditions = $this->Request->getParams('REQUEST','conditions') ?? [];

        // Retrieve the conjunction
        $conjunction = $this->Request->getParams('REQUEST','conjunction') ?? 'AND';

        // Check if the records is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "POST"){

                // Retrieve the services records
                $message['data']['records'] = $this->Model->{$this->name}->fetchAll($conditions, $conjunction);
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Retrieve a single record
     */
    public function fetchAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the id
        $id = $this->Request->getParams('REQUEST','id') ?? null;

        // Check if the records is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "GET"){

                // Retrieve the record
                $record = $this->Model->{$this->name}->fetch($id);

                // Check if the record was found
                if(!empty($record)){

                    // Check if the organization owns the record
                    if(!array_key_exists('organization',$record) || $record['organization']['id'] == $this->Auth->user()->organization()->id){

                        // Check if the user is authorized to access the record
                        if(
                            $this->Auth->isAuthorized("AccountManager", 1) ||
                            (array_key_exists('task',$record) && array_key_exists('assignedTo',$record['task']) && is_null($record['task']['assignedTo'])) ||
                            (array_key_exists('task',$record) && array_key_exists('assignedTo',$record['task']) && $record['task']['assignedTo'] == $this->Auth->user()->id) ||
                            (array_key_exists('assignedTo',$record) && is_null($record['assignedTo'])) ||
                            (array_key_exists('assignedTo',$record) && $record['assignedTo'] == $this->Auth->user()->id)
                        ){

                            // Retrieve the record
                            $message['data']['record'] = $record;

                            // Add the dependencies
                            $message['data']['dependencies'] = [];
                        } else {
                            $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
                        }
                    } else {
                        $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
                    }
                } else {
                    $message = ["status" => 404, "message" => "Not Found", "data" => "The requested ".$this->name." was not found."];
                }
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Create a record
     */
    public function createAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "POST"){

                // Retrieve the parameters
                $parameters = $this->Request->getParams('REQUEST');

                // Add support for Locales
                if(!array_key_exists('locale',$parameters) || empty($parameters['locale'] || is_null($parameters['locale']))){
                    $parameters['locale'] = $this->Locale->current();
                }

                // Check if all required fields are set
                if(count(array_intersect_key(array_flip($this->required), $parameters)) == count($this->required)){

                    // Insert the parameters
                    $message['data']['parameters'] = $parameters;

                    // Create the record
                    $id = $this->Model->{$this->name}->create($parameters);

                    // Check if the record was created
                    if($id){

                        // Retrieve the record
                        $message['data']['record'] = $this->Model->{$this->name}->fetch($id);

                        // Check if the record was created
                        if(empty($message['data']['record'])){
                            $message = ["status" => 500, "message" => "Internal Server Error", "data" => "An error occurred while retrieving the created ".$this->name."."];
                        }
                    } else {
                        $message = ["status" => 500, "message" => "Internal Server Error", "data" => "An error occurred while creating the ".$this->name."."];
                    }
                } else {
                    $message = ["status" => 400, "message" => "Bad Request", "data" => "Some required fields are missing [".implode(", ", array_diff($this->required, array_keys($parameters)))."]"];
                }
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Update a record
     */
    public function updateAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the record
        $record = $this->Model->{$this->name}->fetch(intval($this->Request->getParams('GET','id')));

        // Check if the record is accessible
        if(empty($record)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested ".$this->name."."];
        } else {
            // Check if the organization owns the record
            if(array_key_exists('organization',$record) && $record['organization']['id'] != $this->Auth->user()->organization()->id){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
            }
            // Check if the user is authorized to access the record
            if(array_key_exists('assignedTo',$record) && $record['assignedTo']['id'] != $this->Auth->user()->id && !$this->Auth->isAuthorized("AccountManager", 1)){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
            }
        }

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "POST"){

                // Retrieve the parameters
                $parameters = $this->Request->getParams('REQUEST');

                // Insert the parameters
                $message['data']['parameters'] = $parameters;

                // Update the record
                $affectedRows = $this->Model->{$this->name}->update($record['id'], $parameters);

                // Check if the record was updated
                if($affectedRows){

                    // Retrieve the record
                    $message['data']['record'] = $this->Model->{$this->name}->fetch($record['id']);
                } else {
                    $message = ["status" => 500, "message" => "Internal Server Error", "data" => "An error occurred while updating the ".$this->name."."];
                }
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Delete a record
     */
    public function deleteAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the record
        $record = $this->Model->{$this->name}->fetch(intval($this->Request->getParams('GET','id')));

        // Check if the record is accessible
        if(empty($record)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested ".$this->name."."];
        } else {
            // Check if the organization owns the record
            if(array_key_exists('organization',$record) && $record['organization']['id'] != $this->Auth->user()->organization()->id){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
            }
            // Check if the user is authorized to access the record
            if(array_key_exists('assignedTo',$record) && $record['assignedTo']['id'] != $this->Auth->user()->id && !$this->Auth->isAuthorized("AccountManager", 1)){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
            }
        }

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "GET"){

                // Delete the record
                $affectedRows = $this->Model->{$this->name}->delete($record['id']);

                // Check if the record was deleted
                if($affectedRows){

                    // Retrieve the record
                    $message['data']['record'] = $record;
                } else {
                    $message = ["status" => 500, "message" => "Internal Server Error", "data" => "An error occurred while deleting the ".$this->name."."];
                }
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Archive a record
     */
    public function archiveAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the record
        $record = $this->Model->{$this->name}->fetch(intval($this->Request->getParams('GET','id')));

        // Check if the record is accessible
        if(empty($record)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested ".$this->name."."];
        } else {
            // Check if the organization owns the record
            if(array_key_exists('organization',$record) && $record['organization']['id'] != $this->Auth->user()->organization()->id){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
            }
            // Check if the user is authorized to access the record
            if(array_key_exists('assignedTo',$record) && $record['assignedTo']['id'] != $this->Auth->user()->id && !$this->Auth->isAuthorized("AccountManager", 1)){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
            }
        }

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "GET"){

                // Update the record
                $this->Model->{$this->name}->archive($record['id']);

                // Retrieve the Updated record
                $message["data"]["record"] = $this->Model->{$this->name}->fetch($record['id']);
            } else {
                $message = ["status" => 400, "message" => "Bad Request", "data" => "Invalid Request Method"];
            }
        }

        return $message;
    }

    /**
     * Recover a record
     */
    public function recoverAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the record
        $record = $this->Model->{$this->name}->get(intval($this->Request->getParams('GET','id')));

        // Check if the record is accessible
        if(empty($record)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested ".$this->name."."];
        } else {
            // Check if the organization owns the record
            if(array_key_exists('organization',$record) && $record['organization']['id'] != $this->Auth->user()->organization()->id){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
            }
            // Check if the user is authorized to access the record
            if(array_key_exists('assignedTo',$record) && $record['assignedTo']['id'] != $this->Auth->user()->id && !$this->Auth->isAuthorized("AccountManager", 1)){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this ".$this->name."."];
            }
        }

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "GET"){

                // Update the record
                $this->Model->{$this->name}->recover($record['id']);

                // Retrieve the Updated record
                $message["data"]["record"] = $this->Model->{$this->name}->fetch($record['id']);
            } else {
                $message = ["status" => 400, "message" => "Bad Request", "data" => "Invalid Request Method"];
            }
        }

        return $message;
    }

    /**
     * Retrieve the table definitions
     */
    public function describeAction():array
    {
        return ["status" => 200,"message" => "OK","data" => $this->Model->{$this->name}->describe()];
    }
}
