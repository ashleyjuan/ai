<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

require '../vendor/autoload.php';
require '../vendor/TCPDF/tcpdf.php';


// $config['displayErrorDetails'] = true;
// $config['addContentLengthHeader'] = false;

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        "determineRouteBeforeAppMiddleware" => true
    ],
];
$app = new \Slim\App($config);
$container = $app->getContainer();

// $container['db'] = function ($c) {
//     // $connection = new PDO('pgsql:host=140.127.49.168;dbname=sugoeat', 'sugoeat', '7172930');
//     // return $connection;
//     // $dbhost = '172.24.40.106,14335'; //old
//     $dbhost = '172.0.4.234,1433';
//     // $dbport = '';
//     $dbuser = 'nknu';
//     $dbpasswd = '7172930';
//     $dbname = 'Literacy';
//     $dsn = "sqlsrv:server=$dbhost;Database=$dbname";
//     try {

//         $conn = new PDO($dsn, $dbuser, $dbpasswd);
//         // $conn->exec("SET CHARACTER SET utf8;");
//         // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//         //echo "Connected Successfully";
//     } catch (PDOException $e) {
//         echo "Connection failed: " . $e->getMessage();
//         exit(0);
//     }
//     return $conn;
// };
$container['logging'] = "";

// class loginCheck
// {
//     private $router;

//     public function __construct($router)

//     {
//         $this->router = $router;
//     }

//     public function __invoke($request, $response, $next)
//     {
//         // // $response = $next($request, $response);
//         // // return $response;
//         // $_SESSION['id'] = 8;
//         if (isset($_SESSION['id']) || get_client_ip() == 'UNKNOWN' || get_client_ip() == '::1' || get_client_ip() == '172.17.0.1') {
//             $response = $next($request, $response);
//         } else {
//             $tmppath = $request->getUri()->getPath();
//             $data  = $request->getQueryParams();
//             $tmpurl =  $tmppath;
//             if (isset($data['id'])) {
//                 $tmpurl .= "&id={$data['id']}&file_id_dest={$data['file_id_dest']}";
//             }
//             // var_dump( $tmpurl);



//             $response = $response->withRedirect('/login?url=' . $tmpurl);
//         }
//         return $response;
//     }
// }



// here

$container['view'] = __DIR__ . '/../templates/';
$container['upload_directory'] = __DIR__ . '/../uploads/';
$container['upload_mp3_directory'] = __DIR__ . '/../uploads/mp3/';
$container['sample_directory'] = __DIR__ . '/../uploads/sample/';
$container['upload_file_directory'] = __DIR__ . '/../uploads/UploadFile/';
$container['upload_file_apply_directory'] = __DIR__ . '/../uploads/UploadFile/ApplyFiles/';
$container['layout_picture'] = __DIR__ . '/../uploads/layout/';
$container['resource_picture'] = __DIR__ . '/../uploads/resource/';

$container['learn_layout_picture'] = __DIR__ . '/../uploads/learn/layout/';

$container['island_layout_picture'] = __DIR__ . '/../uploads/island/layout/';

// $app->get('/',  \homecontroller::class . ':home');
$app->group('', function () use ($app) {
    $app->group('/api', function () use ($app) {
        $app->get('/index', function () {
            echo '<h1>Hello World</h1>';
        });
    });

    $app->get('[/{params:.*}]', function ($request, $response, $args) {
        $renderer = new PhpRenderer($this->view);
        $response = $renderer->render($response, 'index.html');
        return $response;
    });
});

$app->run();
