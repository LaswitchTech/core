<?php

/**
 * Core Framework - Session
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;

class Session {

    // Global Properties
    protected $Database;
    protected $UUID;
    protected $Request;
    protected $Log;

    // Properties
    protected $user;
    protected $id;

    /**
     * Constructor
     */
    public function __construct(User $User)
    {
        // Import Global Variables
        global $REQUEST, $UUID, $DATABASE, $LOG;

        // Initialize Properties
        $this->Database = $DATABASE;
        $this->UUID = $UUID;
        $this->Request = $REQUEST;
        $this->Log = $LOG;
        $this->user = $User;
        $this->id = session_id();
    }

    public function create(): self
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table('sessions')
            ->select('*')
            ->filter()
            ->where('id', 9999, '<>')
            ->filter()
            ->where('uuid', $this->UUID->toString("auth-" . $this->id), '=', 'OR')
            ->where('user', $this->user->id(), '=', 'OR');

        // Retrieve any existing session
        $Sessions = $Query->result();

        // Check if their are multiple sessions
        if(count($Sessions) > 1){

            // Create the Query
            $Query = $this->Database->query()
                ->table('sessions')
                ->delete()
                ->filter()
                ->where('id', 9999, '<>')
                ->filter()
                ->where('uuid', $this->UUID->toString("auth-" . $this->id), '=', 'OR')
                ->where('user', $this->user->id(), '=', 'OR')
                ->result();

            // Clear Sessions
            $Sessions = [];
        }

        // Check if the session exists
        if(count($Sessions) > 0){

            // Create the Query
            $Query = $this->Database->query()
                ->table('sessions')
                ->update([
                    'uuid' => $this->UUID->toString("auth-" . $this->id),
                    'user' => $this->user->id(),
                    'ip' => $this->Log->ip(),
                    'agent' => $this->Log->agent(),
                    'host' => $this->Request->getHostAddress(),
                    'activity' => date('Y-m-d H:i:s'),
                ])
                ->filter()
                ->where('id', 9999, '<>')
                ->filter()
                ->where('uuid', $this->UUID->toString("auth-" . $this->id), '=', 'OR')
                ->where('user', $this->user->id(), '=', 'OR');

            // Execute the query
            $AffectedRows = $Query->result();

            // Create the Query
            $Query = $this->Database->query()
                ->table('users')
                ->update(['session' => $Sessions[0]['id']])
                ->where('id', $this->user->id());

            // Execute the query
            $AffectedRows = $Query->result();
        } else {

            // Create the Query
            $Query = $this->Database->query()
                ->table('sessions')
                ->insert([
                    'uuid' => $this->UUID->toString("auth-" . $this->id),
                    'user' => $this->user->id(),
                    'ip' => $this->Log->ip(),
                    'agent' => $this->Log->agent(),
                    'host' => $this->Request->getHostAddress(),
                ]);

            // Execute the query
            $AffectedRows = $Query->result();

            // Get the Last ID
            $SessionId = $Query->lastId();

            // Create the Query
            $Query = $this->Database->query()
                ->table('users')
                ->update(['session' => $SessionId])
                ->where('id', $this->user->id());

            // Execute the query
            $AffectedRows = $Query->result();
        }

        // Set Session
        $this->Request->setParams('SESSION',$this->UUID->toString("auth-" . $this->id), $this->user->id());

        // Check if the user is being authenticated and remember is set
        if($this->Request->getParams('REQUEST','username') && $this->Request->getParams('REQUEST','password') && $this->Request->getParams('REQUEST','remember')){

            // Set Cookie
            setcookie($this->UUID->toString("auth-" . $this->id), $this->user->id(), time() + 60 * 60 * 24 * 30, '/');
        }

        return $this;
    }

    public function clear(): self
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table('sessions')
            ->delete()
            ->filter()
            ->where('id', 9999, '<>')
            ->filter()
            ->where('uuid', $this->UUID->toString("auth-" . $this->id), '=', 'OR')
            ->where('user', $this->user->id(), '=', 'OR')
            ->result();

        // Unset Session
        $this->Request->clearParams('SESSION',$this->UUID->toString("auth-" . $this->id));
        $this->Request->clearParams('COOKIE',$this->UUID->toString("auth-" . $this->id));

        // Destroy Session
        session_destroy();

        // Start Session
        session_start();

        // Regenerate Session ID
        $this->id = session_id();

        return $this;
    }
}
