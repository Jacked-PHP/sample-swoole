<html>
<head>
    <title><?php echo $page ?? 'Admin Page' ?></title>
    <link rel="icon" href="data:,"><!-- This avoids errors for favicon requests -->
</head>
<body>

<?=$this->section('content')?>

<?php if (null !== $ws_context) { ?>
    <?php $this->insert('partials/chat') ?>
    <script>
        window.WS_PORT = <?php echo $ws_context['port'] ?>;
        window.WS_TOKEN = '<?php echo $ws_context['token'] ?>';
    </script>
    <script src="js/ws.js"></script>
<?php } ?>

</body>
</html>
