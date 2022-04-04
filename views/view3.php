<html>
<head>
    <title>Users Page</title>
    <link rel="icon" href="data:,"><!-- This avoids errors for favicon requests -->
</head>
<body>

<h1>User</h1>

<ul>
    <li>(<?=$user['id']?>) <?=$user['name']?> - <?=$user['email']?></li>
</ul>

</body>
</html>