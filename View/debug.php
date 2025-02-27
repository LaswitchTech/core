<?php

// Define the end of line character based on the environment
$_EOL = defined("STDIN") ? PHP_EOL : "<br>";
$_BOLD = defined("STDIN") ? "" : "<strong>";
$_BOLDE = defined("STDIN") ? "" : "</strong>";

// Print host address
echo $_BOLD . "Host" . $_BOLDE . ": " . $this->Request->getHost() . $_EOL;
echo $_BOLD . "Host Address" . $_BOLDE . ": " . $this->Request->getHostAddress() . $_EOL;

// Print Unique request ID
echo $_BOLD . "Unique request ID" . $_BOLDE . ": " . uniqid() . $_EOL;

// Print Request Method
echo $_BOLD . "Request Method" . $_BOLDE . ": " . $this->Request->method() . $_EOL;

// Display the PHP Session Status
echo $_BOLD . "Session" . $_BOLDE . ": " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . $_EOL;

// Check if php session is started
if (session_status() === PHP_SESSION_ACTIVE) {

    // Print session id
    echo $_BOLD . "Session ID" . $_BOLDE . ": " . session_id() . $_EOL;

    // Check if the session should be cleared
    if(!is_null($this->Request->getParams('GET','clear'))){ session_destroy(); }
}

// Check if we should display the defined variables
if(!is_null($this->Request->getParams('GET','vars'))){

    // Display the list of defined global variables
    echo $_EOL . $_BOLD . "Defined Global Variables" . $_BOLDE . ": " . $_EOL;

    // Loop through the defined variables
    foreach(array_merge(get_defined_vars(), $GLOBALS) as $key => $value){

        // Check if $value is empty
        if(empty($value) || in_array($value, [$_EOL, $_BOLD, $_BOLDE])){

            // Skip the variable
            continue;
        }

        // Check if $value is a class
        if(is_object($value)){

            // Retrieve the class name
            $value = get_class($value);
        }

        // Check if $value is an array
        if(is_array($value)){

            // Convert the array to a string
            $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        // Display the variable name and if it is null
        echo $_BOLD . $key . $_BOLDE . " = " . $value . $_EOL;
    }
}

// Check if we should display CSRF
if(!is_null($this->Request->getParams('GET','csrf'))){

    // Check if $this->CSRF is defined
    if(isset($this->CSRF)){

        // Skip a line
        echo $_EOL . $_BOLD . "CSRF Key" . $_BOLDE . ": " . $this->CSRF->key() . $_EOL;
        echo $_BOLD . "CSRF Token" . $_BOLDE . ": " . $this->CSRF->token() . $_EOL;
    }
}

// Check if we should display Auth
if(!is_null($this->Request->getParams('GET','auth'))){

    // Check if $this->Auth is defined
    if(isset($this->Auth)){

        // Display the Auth Status
        echo $_EOL . $_BOLD . "Auth Status" . $_BOLDE . ": " . (!in_array(get_class($this->Auth),["Module","LaswitchTech\Core\Module"]) ? 'true' : 'false') . $_EOL;

        // Check if $this->Auth is not an instance of Strap
        if(!in_array(get_class($this->Auth),["Module","LaswitchTech\Core\Module"])){

            // Display the Auth Status
            echo $_BOLD . "Authentication Status" . $_BOLDE . ": " . ($this->Auth->isAuthenticated() ? 'true' : 'false') . $_EOL;

            // Display the Auth Loaded
            echo $_BOLD . "Authentication Loaded" . $_BOLDE . ": " . ($this->Auth->isLoaded() ? 'true' : 'false') . $_EOL;

            // Check if the user is authenticated
            if($this->Auth->isAuthenticated()){

                // Display the Auth Method
                echo $_BOLD . "Authentication Method" . $_BOLDE . ": " . $this->Auth->method() . $_EOL;
            }

            // $group = $this->Auth->group('Administrator');
            // var_dump($group->members('users'));
            // var_dump($group->members('organizations'));
            // $role = $this->Auth->role('Administrator');
            // var_dump($role->members('users'));
            // var_dump($role->members('organizations'));
            // var_dump($role->permissions());
            // $user = $this->Auth->user('support@laswitchtech.com');
            // var_dump($user->backend()->set('123')->save());
            // var_dump($user->backend()->validate('123'));
        }
    }
}

// Check if we should display Locale
if(!is_null($this->Request->getParams('GET','locales'))){

    // Check if $this->Locale is defined
    if(isset($this->Locale)){

        // Display the LOCALE Status
        echo $_EOL . $_BOLD . "Locale Status" . $_BOLDE . ": " . (!in_array(get_class($this->Locale),["Module","LaswitchTech\Core\Module"]) ? 'true' : 'false') . $_EOL;

        // Check if $this->Locale is not an instance of Strap
        if(!in_array(get_class($this->Locale),["Module","LaswitchTech\Core\Module"])){

            // Display the current LOCALE
            echo $_BOLD . "Current Timezone" . $_BOLDE . ": " . $this->Locale->timezone() . $_EOL;

            // Display the current LOCALE
            echo $_BOLD . "Current Locale" . $_BOLDE . ": " . $this->Locale->current() . $_EOL;

            // List all available LOCALE
            echo $_BOLD . "Available Timezones" . $_BOLDE . ": " . json_encode($this->Locale->timezones(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . $_EOL;

            // List all available LOCALE
            echo $_BOLD . "Available Locale" . $_BOLDE . ": " . json_encode($this->Locale->list(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . $_EOL;

            // Create a new LOCALE
            $en_us = $this->Locale->locale('en-us');

            // Set Language
            $en_us->language('English (United States)');

            // Set Region
            $en_us->region('United States');

            // Set Charset
            $en_us->charset('UTF-8');

            // Add a new translation
            $en_us->add('Hello World', 'Hello World');

            // Output the translation
            echo $_BOLD . "Translation" . $_BOLDE . ": " . $en_us->get('Hello World') . $_EOL;

            // Create a new LOCALE
            $fr_fr = $this->Locale->locale('fr-fr');

            // Set Language
            $fr_fr->language('Francais (France)');

            // Set Region
            $fr_fr->region('France');

            // Set Charset
            $fr_fr->charset('UTF-8');

            // Add a new translation
            $fr_fr->add('Hello World', 'Bonjour le monde');

            // Output the translation
            echo $_BOLD . "Translation" . $_BOLDE . ": " . $fr_fr->get('Hello World') . $_EOL;
        }
    }
}

// Import Global Variables
global $DATABASE, $SMTP, $ROUTER;

// Check if we should display Database
if(!is_null($this->Request->getParams('GET','database'))){

    // Check if $DATABASE is defined
    if(isset($DATABASE)){

        // Display the Database Status
        echo $_EOL . $_BOLD . "Database Status" . $_BOLDE . ": " . $DATABASE->isConnected() . $_EOL;

        // Check if the database is connected
        if ($DATABASE->isConnected()) {

            // Example: INSERT
            echo $_EOL . $_BOLD . "Insert Query" . $_BOLDE . ": " . $_EOL;

            // Create the Query
            $Query = $DATABASE->query()
                ->table('test')
                ->insert(['name' => 'Lorem Ipsum']);

            // Display the Query
            echo $_BOLD . "Query" . $_BOLDE . ": " . $Query . $_EOL;

            // Execute the query
            $Result = $Query->result();

            // Display the Result
            echo $_BOLD . "Result" . $_BOLDE . ": " . $Result . $_EOL;

            // Get the Last ID
            $LastId = $Query->lastId();

            // Display the Result
            echo $_BOLD . "Last ID" . $_BOLDE . ": " . $LastId . $_EOL;

            // Example: UPDATE
            echo $_EOL . $_BOLD . "Update Query" . $_BOLDE . ": " . $_EOL;

            // Create the Query
            $Query = $DATABASE->query()
                ->table('test')
                ->update(['name' => 'Dolor Sit'])
                ->where('id', $LastId, '=');

            // Display the Query
            echo $_BOLD . "Query" . $_BOLDE . ": " . $Query . $_EOL;

            // Execute the query
            $Result = $Query->result();

            // Display the Result
            echo $_BOLD . "Result" . $_BOLDE . ": " . $Result . $_EOL;

            // Example: SELECT
            echo $_EOL . $_BOLD . "Select Query" . $_BOLDE . ": " . $_EOL;

            // Create the Query
            $Query = $DATABASE->query()
                ->table('test')
                ->select('id, name')
                ->order('id', 'DESC')
                ->limit(1);

            // Display the Query
            echo $_BOLD . "Query" . $_BOLDE . ": " . $Query . $_EOL;

            // Execute the query
            $Result = $Query->result();

            // Check if $Result is an array
            if(is_array($Result)){

                // Convert the array to a string
                $Result = json_encode($Result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }

            // Display the Result
            echo $_BOLD . "Result" . $_BOLDE . ": " . $Result . $_EOL;

            // Example: DELETE
            echo $_EOL . $_BOLD . "Delete Query" . $_BOLDE . ": " . $_EOL;

            // Create the Query
            $Query = $DATABASE->query()
                ->table('test')
                ->delete()
                ->where('id', $LastId, '=');

            // Display the Query
            echo $_BOLD . "Query" . $_BOLDE . ": " . $Query . $_EOL;

            // Execute the query
            $Result = $Query->result();

            // Display the Result
            echo $_BOLD . "Result" . $_BOLDE . ": " . $Result . $_EOL;

            // Example: Advanced SELECT
            echo $_EOL . $_BOLD . "Advanced Select Query" . $_BOLDE . ": " . $_EOL;

            // Create the Query
            $Query = $DATABASE->query()
                ->table('users')
                ->select('id, owner.username, organization.name')
                ->join('owner', 'users', 'username')
                ->join('organization', 'organizations', 'id')
                ->index('id')
                ->filter()
                ->where('isDeleted', 0)
                ->where('id', 9999, '<>')
                ->limit(1);

            // Display the Query
            echo $_BOLD . "Query" . $_BOLDE . ": " . $Query . $_EOL;

            // Execute the query
            $Result = $Query->result();

            // Check if $Result is an array
            if(is_array($Result)){

                // Convert the array to a string
                $Result = json_encode($Result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }

            // Display the Result
            echo $_BOLD . "Result" . $_BOLDE . ": " . $Result . $_EOL;

            // Example: Describe
            echo $_EOL . $_BOLD . "Describe" . $_BOLDE . ": " . $_EOL;

            // Create the Schema
            $Schema = $DATABASE->schema()
                ->define('test');

            // Describe the table
            $Result = $Schema->describe();

            // Check if $Result is an array
            if(is_array($Result)){

                // Convert the array to a string
                $Result = json_encode($Result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }

            // Display the Result
            echo $_BOLD . "Result" . $_BOLDE . ": " . $Result . $_EOL;

            // Example: Compare
            echo $_EOL . $_BOLD . "Compare" . $_BOLDE . ": " . $_EOL;

            // Compare Local and Remote Schema
            $Result = $Schema->compare();

            // Check if $Result is an array
            if(is_array($Result)){

                // Convert the array to a string
                $Result = json_encode($Result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }

            // Display the Result
            echo $_BOLD . "Result" . $_BOLDE . ": " . $Result . $_EOL;

            // Compare Local and Remote Schema
            $Result = $Schema->compare(true);

            // Check if $Result is an array
            if(is_array($Result)){

                // Convert the array to a string
                $Result = json_encode($Result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }

            // Display the Result
            echo $_BOLD . "Result" . $_BOLDE . ": " . $Result . $_EOL;
        }
    }
}

// Check if we should display SMTP
if(!is_null($this->Request->getParams('GET','smtp'))){

    // Check if $SMTP is defined
    if(isset($SMTP)){

        // Display the SMTP Status
        echo $_EOL . $_BOLD . "SMTP Status" . $_BOLDE . ": " . (!in_array(get_class($SMTP),["Module","LaswitchTech\Core\Module"]) ? 'true' : 'false') . $_EOL;

        // Check if $SMTP is not an instance of Strap
        if(!in_array(get_class($SMTP),["Module","LaswitchTech\Core\Module"])){

            // Connect to the SMTP Server
            $SMTP->connect();

            // Display the SMTP Status
            echo $_BOLD . "SMTP Connection Status" . $_BOLDE . ": " . ($SMTP->isConnected() ? 'true' : 'false') . $_EOL;

            // Check if the SMTP Server is connected
            if($SMTP->isConnected()){

                // Authenticate to the SMTP Server
                $SMTP->authenticate();

                // Display the SMTP Status
                echo $_BOLD . "SMTP Authentication Status" . $_BOLDE . ": " . ($SMTP->isAuthenticated() ? 'true' : 'false') . $_EOL;

                // Check if the SMTP Server is authenticated
                if($SMTP->isAuthenticated()){

                    // Create a new message
                    $message = $SMTP->message()
                        ->to('support@laswitchtech.com')
                        ->subject('Test Email from %HOST%')
                        ->body('This is a test email')
                        ->var('logo', 'data:image/png;base64,' . base64_encode(file_get_contents($this->Config->root() . '/src/icons/icon.png')))
                        ->var('brand', 'LaswitchTech')
                        ->var('greetings', "Sincerely,<br>LaswitchTech's Team");

                    // Send the message
                    $message->send();

                    // Display the SMTP Status
                    echo $_BOLD . "Message Status" . $_BOLDE . ": " . ($message->status() ? 'true' : 'false') . $_EOL;

                    // Save the message
                    $message->save();

                    // Display the SMTP Status
                    echo $_BOLD . "Message Path" . $_BOLDE . ": " . $message->path() . $_EOL;
                }
            }
        }
    }
}

// Check if we should display Router
if(!is_null($this->Request->getParams('GET','router'))){

    // Check if $ROUTER is defined
    if(isset($ROUTER)){

        // Display the LOCALE Status
        echo $_EOL . $_BOLD . "Router Status" . $_BOLDE . ": " . (!in_array(get_class($ROUTER),["Module","LaswitchTech\Core\Module"]) ? 'true' : 'false') . $_EOL;

        // Check if $this->Locale is not an instance of Strap
        if(!in_array(get_class($ROUTER),["Module","LaswitchTech\Core\Module"])){

            // Display the current Route
            echo $_BOLD . "Current Route" . $_BOLDE . ": " . $ROUTER->route('/',['view' => 'index'])->namespace() . $_EOL;
        }
    }
}
