<!--
  Core Framework - View File

  @license MIT (https://mit-license.org/)
  @author  Louis Ouellet <louis@laswitchtech.com>
-->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Authentication</title>
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
            div {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: left;
            }
            h1 {
                font-size: 4rem;
                margin: 0 0 1rem;
            }
            p {
                font-size: 1.2rem;
                margin: 0 0 2rem;
            }
            form {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: left;
            }
            input {
                padding: 0.8rem 1.2rem;
                text-decoration: none;
                transition: background 0.3s ease;
                font-family: monospace;
                text-wrap-mode: nowrap;
                white-space-collapse: preserve;
                border-radius: 0px;
                margin-left: 1rem;
                margin-right: 1rem;
            }
            input:focus {
                border-color: blue;
            }
            input[type="email"] {
                border-top-left-radius: 4px;
                border-top-right-radius: 4px;
                border-bottom-width: 0px;
            }
            input[type="password"] {
                border-top-width: 1px;
                border-bottom-left-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            button {
                margin-top: 1rem;
                margin-left: 1rem;
                margin-right: 1rem;
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
            button:hover {
                background: #555;
            }
        </style>
    </head>
    <body>

        <div>
            <h1>Authentication</h1>
            <form action="/" method="POST">
                <?= $this->CSRF->field(); ?>
                <input type="email" name="username" placeholder="username@domain.com" required>
                <input type="password" name="password" placeholder="password" required>
                <button type="submit">Authenticate</button>
            </form>
        </div>

    </body>
</html>

