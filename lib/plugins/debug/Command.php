<?php

/**
 * Core Framework - DebugCommand
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use LaswitchTech\Core\Abstracts\Command;

class DebugCommand extends Command {

    /**
     * Constructor
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the command
     */
    public function executeAction(){
        $this->Output->print("Debugging...");
    }
}
