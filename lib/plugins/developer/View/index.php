<?php
/**
 * Developer Tools Page
 */

// Import Global Variables
global $AUTH, $VIEW, $REQUEST;

?>
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-tools"></i> Developer Tools</h1>
            <p>Access and manage developer tools and scaffolding features.</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Development Mode</h5>
                </div>
                <div class="card-body">
                    <p>Current development mode status: 
                        <?php if ($data['development_mode']): ?>
                            <span class="badge bg-success">Enabled</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Disabled</span>
                        <?php endif; ?>
                    </p>
                    
                    <button type="button" class="btn btn-primary" onclick="toggleDevelopmentMode()">
                        <?php echo $data['development_mode'] ? 'Disable' : 'Enable'; ?> Development Mode
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Scaffold Generator</h5>
                </div>
                <div class="card-body">
                    <p>Generate new components using pre-defined templates.</p>
                    
                    <form id="scaffoldForm" class="row g-3">
                        <div class="col-md-6">
                            <label for="scaffoldType" class="form-label">Component Type</label>
                            <select class="form-select" id="scaffoldType" name="type">
                                <?php foreach ($data['scaffold_templates'] as $template): ?>
                                    <option value="<?php echo $template; ?>"><?php echo ucfirst($template); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="scaffoldName" class="form-label">Component Name</label>
                            <input type="text" class="form-control" id="scaffoldName" name="name" placeholder="e.g., MyNewPlugin">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-wrench"></i> Generate Component
                            </button>
                        </div>
                    </form>
                    
                    <div id="scaffoldResult" class="mt-3" style="display: none;">
                        <h6>Generation Results</h6>
                        <pre id="scaffoldOutput"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Installed Plugins</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($data['plugins'])): ?>
                        <p>No plugins found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Plugin Name</th>
                                        <th>Version</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['plugins'] as $plugin): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($plugin['name']); ?></td>
                                        <td><?php echo htmlspecialchars($plugin['version']); ?></td>
                                        <td><?php echo htmlspecialchars($plugin['description']); ?></td>
                                        <td><span class="badge bg-success">Enabled</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Development Mode:</dt>
                        <dd class="col-sm-9"><?php echo $data['development_mode'] ? 'Enabled' : 'Disabled'; ?></dd>
                        <dt class="col-sm-3">Maintenance Mode:</dt>
                        <dd class="col-sm-9"><?php echo $data['maintenance_mode'] ? 'Enabled' : 'Disabled'; ?></dd>
                        <dt class="col-sm-3">Installer Mode:</dt>
                        <dd class="col-sm-9"><?php echo $data['installer_mode'] ? 'Enabled' : 'Disabled'; ?></dd>
                        <dt class="col-sm-3">Logger Level:</dt>
                        <dd class="col-sm-9"><?php echo $data['logger_level']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDevelopmentMode() {
    // Simple implementation - would connect to backend API in real usage
    alert('Development mode toggle would be implemented here');
}

document.getElementById('scaffoldForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultDiv = document.getElementById('scaffoldResult');
    const outputDiv = document.getElementById('scaffoldOutput');
    
    // In a real implementation, this would make an AJAX call to the backend
    // For now, we'll just mock it
    
    const response = {
        status: 200,
        message: "Component generated successfully",
        data: {
            type: formData.get('type'),
            name: formData.get('name'),
            timestamp: new Date().toISOString()
        }
    };
    
    outputDiv.textContent = JSON.stringify(response, null, 2);
    resultDiv.style.display = 'block';
});
</script>