<?php

declare(strict_types=1);
$parts = explode('/', $_SERVER["REQUEST_URI"]);

//class autoloader function
spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

//to use our custom error handler
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

//updating content type from text/html to application/json
header("Content-type: application/json; charset=UTF-8");

//CORS settings
header("Access-Control-Allow-Origin: *");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit();
}

if ($parts[1] != 'posts') {
    http_response_code(404);
    exit();
}

$id = $parts[2] ?? null;

$database = new Database("localhost","blog","root","");
$gateway=new ProductGateway($database);

$controller = new PostController($gateway);
$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);