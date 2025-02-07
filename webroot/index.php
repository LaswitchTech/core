<pre>
<?php
// Load Composer's autoloader
require_once dirname(__DIR__) . "/vendor/autoload.php";

// Initiate Bootstrap
$BOOTSTRAP = new LaswitchTech\Core\Bootstrap("Router");
?>
</pre>
<!DOCTYPE html>
<html>
    <head>
        <title>Debug</title>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    </head>
    <body style="padding: 20px;">
        <p>
            <a href="/"><h1>Debug</h1></a>
        </p>
        <p>
            <?php if(isset($AUTH) && !in_array(get_class($AUTH),["Module","LaswitchTech\Core\Module"])): ?>
                <?php if(!$AUTH->isAuthenticated()): ?>
                    <form action="/" method="POST">
                        <?= $CSRF->field(); ?>
                        <input type="email" name="username" placeholder="username@domain.com" required>
                        <input type="password" name="password" placeholder="password" required>
                        <button type="submit">Login</button>
                    </form>
                <?php else: ?>
                    <a href="/?logout"><button type="button">Logout</button></a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="/?vars"><button type="button">Show Defined Variables</button></a>
            <a href="/?clear"><button type="button">Clear Session</button></a>
            <a href="/?send"><button type="button">Send Message</button></a>
            <a href="/?db"><button type="button">Test Database</button></a>
        </p>
        <p><pre id="success" style="background-color: green; color: white; padding: 20px; display: none;"></pre></p>
        <p><pre id="error" style="background-color: red; color: white; padding: 20px; display: none;"></pre></p>
        <p><pre><?php require_once __DIR__ . '/debug.php'; ?></pre></p>
    </body>
</html>
<script>
    $(document).ready(function(){
        $.ajax({
            url: 'api.php/debug/execute',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Success:', response);
                for(const [key, value] of Object.entries(response)){
                    $('#success').append(`<strong>${key}</strong> = ${value}<br>`).show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', status, error);
                for(const [key, value] of Object.entries({status: status, error: error})){
                    $('#error').append(`<strong>${key}</strong> = ${value}<br>`).show();
                }
            }
        });
    });
</script>
