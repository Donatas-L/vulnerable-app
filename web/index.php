<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app['db'] = function() {
    return new PDO('mysql:host=localhost;dbname=vulnerable', 'vulnerable', 'vulnerable');
};

$app->get("/", function () {
    return "Hello world!";
});

$app->get('/profile', function(Silex\Application $app){
    $db = $app['db'];
    
    // Possible SQL injection
    $id = $_GET['id'];
    $statement = $db->query("SELECT * FROM users WHERE id = $id");

    $results = $statement->fetchAll();
    $user = $results[0];

    // Possible XSS attack
    return <<<EOF
    <dl>
    <dt>Username:</dt><dd>{$user['username']}</dd>
    <dt>Email:</dt><dd>{$user['email']}</dd>
    </dl>
EOF;
});
$app->get('/login', function() {
    
    // Plain text password
    return <<<EOF
    <form action="/login" method="post">
    <label>Username: <input type="text" name="username" /></label>
    <label>Password: <input type="password" name="password" /></label>

    <input type="submit" value="submit" />
    </form>
EOF;
});

$app->post('/login', function(Silex\Application $app) {
    $db = $app['db'];

    // Possible SQL injection
    $username = $_POST['username'];
    $password = $_POST['password'];
    $statement = $db->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");

    $results = $statement->fetchAll();
    if(count($results) > 0) {
        return "Authenticated as " . $results[0]['username'];
    } else {
        return "Invalid username/password";
    }
});

$app->run();