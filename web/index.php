<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => 'mysql',
        'user' => 'vulnerable',
        'password' => 'vulnerable',
        'dbname' => 'vulnerable',
        'charset' => 'utf8',
    ),
));

$app->get("/", function() use($app) {
    return "Hello";
});

$app->get('/profile', function (Request $request) use($app) {
    $db = $app['db'];
    $id = $request->query->get('id');
    
    // Possible SQL injection
    $sql = "SELECT * FROM users WHERE id = $id";

    $user = $app['db']->fetchAssoc($sql, array((int) $id));

    // Possible XSS attack
    return <<<EOF
    <dl>
    <dt>Username:</dt><dd>{$user['username']}</dd>
    <dt>Email:</dt><dd>{$user['email']}</dd>
    </dl>
EOF;
});
$app->get('/login', function() use($app) {
    
    // Plain text password
    return <<<EOF
    <form action="/login" method="post">
    <label>Username: <input type="text" name="username" /></label>
    <label>Password: <input type="password" name="password" /></label>

    <input type="submit" value="submit" />
    </form>
EOF;
});

$app->post('/login', function (Request $request) use($app) {
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