<?php

session_start();

require_once "config.php";

/*$path = 'myfolder/myimage.png';
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

$file = $_FILES["fileToUpload"];

$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];

$file_ext = explode('.', $file_name);
$file_ext = strtolower(end($file_ext));

$file_name_new = $file_name;
$file_destition = 'v/' . $file_name_new;*/

if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true) {
    $sql = "SELECT name, Vname, CustomVname, followers, partner, channelIMG FROM channels WHERE email = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_SESSION["email"]);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $name, $VnameCH, $CustomVname, $followers, $partner, $channelIMG);
                mysqli_stmt_fetch($stmt);
            }
        }
    }
}

?>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Adrian Rzeszutek">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.0/css/all.css" crossorigin="anonymous">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="img/favicon.png" type="image/x-icon">
    <title>Treści na kanale - Odtwarzacz</title>
</head>
<style>
    <?php include 'MainStyle.css'; ?>
</style>
<body>
<nav class="navbar">
    <div class="toggle-btn">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <img src="img/logo.png" class="logo" onclick="window.open('index.php', '_self')">
    <div class="search-box">
        <form class="flex-search" action="<?php echo htmlspecialchars('/players/result.php'); ?>" method="GET">
            <input type="text" id="search" name="query" class="search-bar" placeholder="Szukaj treści na swoim kanale" autocomplete="off">
            <button class="search-btn" id="search-btn"><i class="fa fa-search" aria-hidden="true"></i></button>
        </form>
        <span onclick="startDictation()"><i class="fa fa-microphone icon"></i></span>
    </div>
    <div class="user-options">
        <span><i class="fa fa-video icon"></i></span>
        <span><i class="fa fa-server icon"></i></span>
        <span><i class="fa fa-bell icon"></i></span>
        <?php
        if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true) {
            echo '<div class="user-dp"><img src="' . $channelIMG . '" onclick="Showchannel()" alt=""></div>';
        } else {
            echo '<span onclick="window.open(\'../auth/login\', \'_self\')"><i class="fa fa-user icon"></i></span>';
        }
        ?>
    </div>
</nav>
<div class="container">
    <!-- make invisible input for uploading files -->
    <div class="upload-container">
        <label for="upload">
            <div style="height: 563px; width: 618.59375px; display: flex; flex-direction: column; justify-content: center; align-items: center; padding-left: 50px; padding-right: 50px; padding-top: 16px;">
                <div class="icon-upload">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M105.4 182.6c12.5 12.49 32.76 12.5 45.25 .001L224 109.3V352c0 17.67 14.33 32 32 32c17.67 0 32-14.33 32-32V109.3l73.38 73.38c12.49 12.49 32.75 12.49 45.25-.001c12.49-12.49 12.49-32.75 0-45.25l-128-128C272.4 3.125 264.2 0 256 0S239.6 3.125 233.4 9.375L105.4 137.4C92.88 149.9 92.88 170.1 105.4 182.6zM480 352h-160c0 35.35-28.65 64-64 64s-64-28.65-64-64H32c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h448c17.67 0 32-14.33 32-32v-96C512 366.3 497.7 352 480 352zM432 456c-13.2 0-24-10.8-24-24c0-13.2 10.8-24 24-24s24 10.8 24 24C456 445.2 445.2 456 432 456z"/></svg>
                </div>
                <input type="file" id="upload" style="display:none">
            </div>
        </label>
    </div>
</div>
</body>
</html>
