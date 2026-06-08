<?php

// Declaring namespace
namespace LaswitchTech\Core\Plugin;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Controller;
use LaswitchTech\Core\Objects\AuditLogger;

class AuditEndpoint extends Controller {

	/**
	 * Index action for audit log page
	 */
	public function indexAction() {
		// Import Global Variables
		global $AUTH, $VIEW, $REQUEST;

        // Check if user is authenticated and has proper permissions
        if (!$AUTH->isAuthenticated() || !$AUTH->isAuthorized('audit', 3)) {
            header('Location: ?login');
            exit;
        }

        // Create audit logger instance
        $AuditLogger = new AuditLogger();

        // Get filter parameters
        $type = $REQUEST->getParams('GET', 'type') ?? 'all';
        $limit = (int)($REQUEST->getParams('GET', 'limit') ?? 50);

        // Build filters
        $filters = [];
        if ($type !== 'all') {
            $filters['type'] = $type;
        }

        // Get audit logs
        $logs = $AuditLogger->get($filters, $limit);

        // Prepare data for template
        $data = [
            'title' => 'Audit Log',
            'logs' => $logs,
            'filter_type' => $type,
            'limit' => $limit
        ];

        // Render the admin audit log page
		$VIEW->render('admin/audit/index', $data);
	}
}