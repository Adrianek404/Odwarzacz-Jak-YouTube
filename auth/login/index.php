<?php
session_start();
require_once '../../config.php';

$email = $password = "";
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["_email"]))) {
        $error = "Podaj email.";
    } else {
        $email = trim($_POST["_email"]);
    }
    if (empty(trim($_POST["_password"]))) {
        $error = "Wpisz hasło";
    } else {
        $password = trim($_POST["_password"]);
    }
    if (empty($error)) {
        $sql = "SELECT id, password,email FROM users WHERE email = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $hashed_password, $email);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["email"] = $email;
                            header('Location: ../../index.php');
                        } else {
                            $error = "Nie poprawny login lub hasło. 2";
                        }
                    }
                } else {
                    $error = "Nie poprawny login lub hasło. 1";
                }
            } else {
            }

            mysqli_stmt_close($stmt);
        }
    } else {
        $error = "Błędny token CSRF.";
    }

}
?>
<!doctype html>

<html lang="pl">
<head>
    <meta name="google-signin-scope" content="profile email">
    <meta name="author" content="Adrian Rzeszutek">
    <meta name="google-signin-client_id"
          content="849860593529-855b9vhlapflggtr6rf550gkuchid020.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="icon" type="image/x-icon" href="../../img/favicon.png">
    <title>Logowanie - Odtwarzacz</title>
</head>
<style>
    .form {
        justify-content: center !important;
        display: flex;
        flex-wrap: wrap;
        margin-left: -15px;
        margin-right: -15px;
        margin-top: 1rem !important;
    }

    .cont {
        margin: auto;
        padding-left: 15px;
        padding-right: 15px;
        width: 100%;
    }

    body {
        position: relative;
        background-color: #383535;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    *:focus {
        outline: none;
    }
</style>
<body>
<div class="cont">
    <div class="form">
        <div class="col-lg-4 col-md-5 p-4" style="background-color: #424242;">
            <h2 class="mb-2">Logowanie do konta</h2>
            <p class="text-muted">Uzupełnij poniższy formularz</p>
            <hr>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <p class="text-white">Logowanie:</p>
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        </div>
                        <input class="form-control" type="email" name="_email" placeholder="Email" required>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        </div>
                        <input class="form-control" type="password" name="_password" placeholder="Hasło" required>
                    </div>
                </div>
                <div class="font-sm text-muted mb-4 px-1">
                    <div class="mb-1">
                        <p class="text-left">Nie masz konta?
                            <a target="_self" class="text-white btn-link px-0" href="/players/auth/register/">kliknij
                                tutaj</a></p>
                    </div>
                </div>
                <button type="submit" class="btn btn-block btn-success">Zaloguj</button>
            </form>
            <hr>
            <?php
            if (!empty($error)) {
                echo '<div class="alert-danger alert">Błąd: ' . $error . '</div>';
            }
            ?>
            <!--<div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div>-->
        </div>
    </div>
</div>
<!--<a href="#" onclick="signOut();">Sign out</a>-->
<script>
    function signOut() {
        var auth2 = gapi.auth2.getAuthInstance();
        auth2.signOut().then(function () {
            console.log('User signed out.');
        });
    }
</script>
<script>
    /*function onSignIn(googleUser) {
        // Useful data for your client-side scripts:
        var profile = googleUser.getBasicProfile();
        console.log("ID: " + profile.getId()); // Don't send this directly to your server!
        console.log('Full Name: ' + profile.getName());
        console.log('Given Name: ' + profile.getGivenName());
        console.log('Family Name: ' + profile.getFamilyName());
        console.log("Image URL: " + profile.getImageUrl());
        console.log("Email: " + profile.getEmail());
        // The ID token you need to pass to your backend:
        var id_token = googleUser.getAuthResponse().id_token;
        console.log("ID Token: " + id_token);
    }*/
</script>
</body>
</html>
