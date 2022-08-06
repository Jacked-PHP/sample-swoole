<?php
$this->layout('templates/admin', [
    'page' => 'Admin Home',
    'ws_context' => $ws_context,
]);
?>

<h1>Hello <?php echo $user_name; ?></h1>

<form action="/logout" method="POST" enctype="multipart/form-data">
    <input name="submit" type="submit" value="Logout"/>
</form>
