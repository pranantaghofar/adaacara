<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <title><?= $title ?></title>
    <?= view('components/noindex_meta') ?>
    <link href="<?= aa_asset_url('assets/admin'); ?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="<?= aa_asset_url('assets/admin'); ?>/vendor/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="<?= aa_asset_url('assets/admin'); ?>/css/ruang-admin.css" rel="stylesheet">

</head>

<body class="bg-gradient-login">
<?php 
 echo view($view);
?>
<script src="<?= aa_asset_url('assets/admin'); ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?= aa_asset_url('assets/admin'); ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= aa_asset_url('assets/admin'); ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= aa_asset_url('assets/admin'); ?>/js/ruang-admin.min.js"></script>
</body>
<script>
setTimeout("$('#ikierror').hide();", 2000);
</script>

</html>
