<!--
  Core Framework - View File

  @license MIT (https://mit-license.org/)
  @author  Louis Ouellet <louis@laswitchtech.com>
-->
<!DOCTYPE html>
<html>
    <head>
        <title>Debug</title>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: #f2f2f2;
                color: #333;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
                font-family: monospace;
                text-wrap-mode: nowrap;
                white-space-collapse: preserve;
                user-select: none;
            }
            h1 {
                font-size: 4rem;
                margin: 0 0 1rem;
            }
            p {
                font-size: 1.2rem;
                margin: 0 0 2rem;
            }
            a {
                background: #333;
                color: #fff;
                padding: 0.8rem 1.2rem;
                text-decoration: none;
                transition: background 0.3s ease;
                font-family: monospace;
                text-wrap-mode: nowrap;
                white-space-collapse: preserve;
                border-radius: 4px;
                cursor: pointer;
            }
            a:hover {
                background: #555;
            }
            div.flex {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: left;
            }
            div.group {
                flex-direction: row;
                align-items: center;
            }
            div.group a {
                margin-top: 0px;
                margin-left: 0px;
                margin-right: 0px;
                border-radius: 0px;
            }
            div.group a.first {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            div.group a.last {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            div.box {
                position: fixed;
                top: 2rem;
            }
            div.box pre {
                border-radius: 8px;
                color: white;
                padding: 20px;
                width: 50vw;
                position: relative;
            }
            div.box pre .close {
                position: absolute;
                top: 0px;
                right: 0px;
                font-size: 1.25rem;
                cursor: pointer;
                padding: 8px 16px;
            }
            #success {
                background-color: green;
            }
            #error {
                background-color: red;
            }
            pre.debug {
                background-color: #333;
                color: #fff;
                padding: 20px;
                width: 50vw;
                margin-top: 2rem;
                border-radius: 8px;
                max-height: 50vh;
                overflow: auto;
            }
        </style>
        <script>
            $(document).ready(function(){
                $('div.box pre .close').click(function(){
                    $(this).parent().hide();
                });
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
    </head>
    <body>

        <div class="box flex">
            <pre class="box" id="success" style="display: none;"><span class="close">x</span></pre>
            <pre class="box" id="error" style="display: none;"><span class="close">x</span></pre>
        </div>

        <div class="flex">
            <h1><?= $this->label() ?></h1>
            <div class="flex group">
                <a class="first" href="/">Refresh</a>
                <a href="/?vars">Variables</a>
                <a href="/?csrf">CSRF</a>
                <a href="/?auth">Auth</a>
                <a href="/?locales">Locales</a>
                <a href="/?database">Database</a>
                <a href="/?smtp">SMTP</a>
                <a href="/?router">Router</a>
                <a class="last" href="/?clear">Clear</a>
            </div>
        </div>

        <div class="flex">
            <pre class="debug"><?php require_once __DIR__ . '/debug.php'; ?></pre>
        </div>
    </body>
</html>
