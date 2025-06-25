<!--
  Core Framework - View File

  @license MIT (https://mit-license.org/)
  @author  Louis Ouellet <louis@laswitchtech.com>
-->
 <!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>403 - Forbidden</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: #f2f2f2;
                font-family: monospace;
                color: #333;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
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
            a {
                background: #333;
                color: #fff;
                padding: 0.8rem 1.2rem;
                text-decoration: none;
                border-radius: 4px;
                transition: background 0.3s ease;
                font-size: 0.7em;
            }
            a:hover {
                background: #555;
            }
        </style>
    </head>
    <body>

        <div>
            <h1>405</h1>
            <p>Oops! The method you used is not allowed on this page.</p>
            <p><a href="/">Go to Homepage</a></p>
        </div>

    </body>
</html>
