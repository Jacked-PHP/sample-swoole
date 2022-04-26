<html>
<head>
    <title>Login Page</title>
    <link rel="icon" href="data:,"><!-- This avoids errors for favicon requests -->

    <style>
        .erro-message {
            color: red;
            margin-bottom: 10px;
        }

        .form-row {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<h1>Login Page</h1>

<div>

    <div class="erro-message"><?=$message?></div>

    <form method="POST" action="/login" enctype="multipart/form-data">
        <div class="form-row">
            <div><label for="email">Email</label></div>
            <div><input name="email" type="text"/></div>
        </div>

        <div class="form-row">
            <div><label for="email">Password</label></div>
            <div><input name="password" type="password"/></div>
        </div>

        <div><input name="submit" type="submit" value="Submit"/></div>
    </form>

</div>

</body>
</html>