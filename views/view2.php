<html>
<head>
    <title>Users Page</title>
    <link rel="icon" href="data:,"><!-- This avoids errors for favicon requests -->
</head>
<body>

<h1>Users</h1>

<ul>
    <?php foreach($users as $user) { ?>
        <li>(<?=$user['id']?>) <?=$user['name']?> - <?=$user['email']?></li>
    <?php } ?>
</ul>

</body>
</html>