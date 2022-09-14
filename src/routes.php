<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection;

$routes->add('index', new Route('/', ['action' => 'index', 'public' => true]));
$routes->add('register', new Route('/register', ['action' => 'register', 'public' => true]));
$routes->add('image', new Route('/image', ['action' => 'image', 'public' => true]));

$routes->add('board', new Route('/board', ['action' => 'board']));
$routes->add('logout', new Route('/logout', ['action' => 'logout']));
$routes->add('upload', new Route('/upload', ['action' => 'upload']));
$routes->add('team-leaderboard', new Route('teams', ['action' => 'leaderboardTeams']));
$routes->add('people-leaderboard', new Route('people', ['action' => 'leaderboardPeople']));
$routes->add('my-team', new Route('/my-team', ['action' => 'myTeam']));
$routes->add('rules', new Route('/rules', ['action' => 'rules']));
$routes->add('delete-activity', new Route('/delete-activity', ['action' => 'deleteActivity']));
$routes->add('edit-team', new Route('/edit-team', ['action' => 'editTeam']));
$routes->add('participate', new Route('/participate', ['action' => 'participate']));
$routes->add('gpx', new Route('/gpx', ['action' => 'downloadGpx']));

$routes->add('admin', new Route('/admin', ['action' => 'admin', 'admin' => true]));
$routes->add('add-team', new Route('/admin/add-team', ['action' => 'addTeam', 'admin' => true]));
$routes->add('assign-team', new Route('/admin/assign-team', ['action' => 'assignTeam', 'admin' => true]));
$routes->add('unassign-team', new Route('/admin/unassign-team', ['action' => 'unassignTeam', 'admin' => true]));
$routes->add('delete-team', new Route('/admin/delete-team', ['action' => 'deleteTeam', 'admin' => true]));
$routes->add('impersonate', new Route('/admin/impersonate', ['action' => 'impersonate', 'admin' => true]));
$routes->add('enable-upload', new Route('/admin/enable-upload', ['action' => 'enableUpload', 'admin' => true]));
$routes->add('edit-rules', new Route('/admin/edit-rules', ['action' => 'editRules', 'admin' => true]));
$routes->add('set-participating', new Route('/admin/set-participating', ['action' => 'setParticipating', 'admin' => true]));
$routes->add('mark-all-as-not-participating', new Route('/admin/set-all-not-participating', ['action' => 'setAllAsNotParticipating', 'admin' => true]));
$routes->add('reset-password', new Route('/admin/reset-password', ['action' => 'resetPassword', 'admin' => true]));
$routes->add('announcement', new Route('/announcement', ['action' => 'announcement', 'admin' => true]));

$routes->add('migrate', new Route('/migrate', ['action' => 'migrate', 'admin' => true]));

return $routes;