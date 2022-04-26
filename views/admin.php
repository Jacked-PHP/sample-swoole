<html>
<head>
    <title>Admin Page</title>
    <link rel="icon" href="data:,"><!-- This avoids errors for favicon requests -->
</head>
<body>

<h1>Hello <?php echo $user_name; ?></h1>

<form action="/logout" method="POST" enctype="multipart/form-data"><input type="submit" value="Logout"/></form>

</body>
</html>