<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/TeacherController.php';
require_once __DIR__ . '/../app/controllers/TeamController.php';
require_once __DIR__ . '/../app/controllers/ParticipantController.php';

init_session();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/') redirect('/login');

if ($uri === '/login' && $method === 'GET') AuthController::loginForm();
elseif ($uri === '/login' && $method === 'POST') AuthController::login();
elseif ($uri === '/logout' && $method === 'POST') AuthController::logout();
elseif (preg_match('#^/activate/([a-f0-9]+)$#', $uri, $m) && $method === 'GET') AuthController::activateForm($m[1]);
elseif (preg_match('#^/activate/([a-f0-9]+)$#', $uri, $m) && $method === 'POST') AuthController::activate($m[1]);

elseif ($uri === '/teacher' && $method === 'GET') TeacherController::dashboard();
elseif ($uri === '/teacher/groups' && $method === 'POST') TeacherController::createGroup();
elseif ($uri === '/teacher/teams' && $method === 'POST') TeacherController::createTeam();
elseif ($uri === '/teacher/students' && $method === 'GET') TeacherController::students();
elseif ($uri === '/teacher/students/import' && $method === 'POST') TeacherController::importStudents();
elseif ($uri === '/teacher/templates' && $method === 'POST') TeacherController::createTemplate();
elseif ($uri === '/teacher/assign' && $method === 'POST') TeacherController::assignTemplate();
elseif ($uri === '/teacher/results' && $method === 'GET') TeacherController::globalResults();
elseif ($uri === '/teacher/results/export' && $method === 'GET') TeacherController::exportGlobalCsv();

elseif ($uri === '/team' && $method === 'GET') TeamController::dashboard();
elseif (preg_match('#^/team/instance/(\d+)$#', $uri, $m) && $method === 'GET') TeamController::editInstance((int)$m[1]);
elseif (preg_match('#^/team/instance/(\d+)/card$#', $uri, $m) && $method === 'POST') TeamController::updateCardSorting((int)$m[1]);
elseif (preg_match('#^/team/instance/(\d+)/tree$#', $uri, $m) && $method === 'POST') TeamController::updateTreeTesting((int)$m[1]);
elseif (preg_match('#^/team/instance/(\d+)/status$#', $uri, $m) && $method === 'POST') TeamController::setStatus((int)$m[1]);
elseif (preg_match('#^/team/instance/(\d+)/results$#', $uri, $m) && $method === 'GET') TeamController::results((int)$m[1]);
elseif (preg_match('#^/team/instance/(\d+)/export$#', $uri, $m) && $method === 'GET') TeamController::exportCsv((int)$m[1]);

elseif (preg_match('#^/p/([a-f0-9]+)$#', $uri, $m) && $method === 'GET') ParticipantController::landing($m[1]);
elseif (preg_match('#^/p/([a-f0-9]+)/start$#', $uri, $m) && $method === 'POST') ParticipantController::start($m[1]);
elseif (preg_match('#^/p/([a-f0-9]+)/card$#', $uri, $m) && $method === 'GET') ParticipantController::cardForm($m[1]);
elseif (preg_match('#^/p/([a-f0-9]+)/card$#', $uri, $m) && $method === 'POST') ParticipantController::submitCard($m[1]);
elseif (preg_match('#^/p/([a-f0-9]+)/tree$#', $uri, $m) && $method === 'GET') ParticipantController::treeForm($m[1]);
elseif (preg_match('#^/p/([a-f0-9]+)/tree$#', $uri, $m) && $method === 'POST') ParticipantController::submitTree($m[1]);
else {
    http_response_code(404);
    echo 'Ruta no encontrada';
}
