<html>

<head></head>

<body>
    <?php
    echo $reslt;
    ?>
    <form action="" method="POST">
        <p>
            <label>Username</label>
            <input id="username" value="user_test1" name="username" type="text" required="required" /><br>
        </p>
        <p>
            <label>Password</label>
            <input id="password" name="password" type="password" required="required" />
        </p>
        <br />
        <p>
            <button type="submit" name="submit-login"><span>Login</span></button> <button type="reset"><span>Cancel</span></button>
        </p>
    </form>
</body>

</html>