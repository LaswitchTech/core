<?php

namespace Tests\Traits;

/**
 * Trait for mocking globals in PHPUnit tests.
 *
 * Usage:
 *   class MyTest extends \PHPUnit\Framework\TestCase {
 *       use MockGlobals;
 *
 *       public function testSomething() {
 *           $this->mockGlobals();
 *           // Now $this->CONFIG, $this->AUTH, etc. are available
 *       }
 *   }
 */
trait MockGlobals
{
    protected $CONFIG;
    protected $AUTH;
    protected $REQUEST;
    protected $OUTPUT;
    protected $LOCALE;
    protected $CSRF;
    protected $DATABASE;
    protected $SMS;
    protected $SMTP;
    protected $IMAP;
    protected $SLS;
    protected $HELPER;
    protected $MODEL;
    protected $INSTALLER;
    protected $UPDATER;
    protected $UUID;
    protected $LOG;
    protected $STYLE;
    protected $BUILDER;

    protected function mockGlobals(): void
    {
        $this->CONFIG = new \LaswitchTech\Core\Config('bootstrap');
        $this->REQUEST = new \LaswitchTech\Core\Request();
        $this->OUTPUT = new \LaswitchTech\Core\Output();
        $this->LOCALE = new \LaswitchTech\Core\Locales();
        $this->CSRF = new \LaswitchTech\Core\CSRF();
        $this->DATABASE = new \LaswitchTech\Core\Module();
        $this->SMS = new \LaswitchTech\Core\Module();
        $this->SMTP = new \LaswitchTech\Core\Module();
        $this->IMAP = new \LaswitchTech\Core\Module();
        $this->SLS = new \LaswitchTech\Core\Module();
        $this->HELPER = new \LaswitchTech\Core\Helpers();
        $this->MODEL = new \LaswitchTech\Core\Models();
        $this->INSTALLER = new \LaswitchTech\Core\Module();
        $this->UPDATER = new \LaswitchTech\Core\Module();
        $this->UUID = new \LaswitchTech\Core\UUID();
        $this->LOG = new \LaswitchTech\Core\Log();
        $this->STYLE = new \LaswitchTech\Core\Style();
        $this->BUILDER = new \LaswitchTech\Core\Builder();

        $GLOBALS['CONFIG'] = $this->CONFIG;
        $GLOBALS['AUTH'] = $this->AUTH;
        $GLOBALS['REQUEST'] = $this->REQUEST;
        $GLOBALS['OUTPUT'] = $this->OUTPUT;
        $GLOBALS['LOCALE'] = $this->LOCALE;
        $GLOBALS['CSRF'] = $this->CSRF;
        $GLOBALS['DATABASE'] = $this->DATABASE;
        $GLOBALS['SMS'] = $this->SMS;
        $GLOBALS['SMTP'] = $this->SMTP;
        $GLOBALS['IMAP'] = $this->IMAP;
        $GLOBALS['SLS'] = $this->SLS;
        $GLOBALS['HELPER'] = $this->HELPER;
        $GLOBALS['MODEL'] = $this->MODEL;
        $GLOBALS['INSTALLER'] = $this->INSTALLER;
        $GLOBALS['UPDATER'] = $this->UPDATER;
        $GLOBALS['UUID'] = $this->UUID;
        $GLOBALS['LOG'] = $this->LOG;
        $GLOBALS['STYLE'] = $this->STYLE;
        $GLOBALS['BUILDER'] = $this->BUILDER;
    }
}
