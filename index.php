<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use MeetupLottery\Draw;
use MeetupLottery\Exporter\CSVExporter;

// Init session
$session = new Session();
$session->start();

// Load config
$config = Yaml::parse(__DIR__ . '/app/config.yml');

// Init request
$request = Request::createFromGlobals();

// Init Draw
$draw = new Draw($config, $session);

// Get event ID
$eventId = $session->get('event_id');
$draw->setEventId($eventId);

// Handle POST request
if ($request->server->get('REQUEST_METHOD') == 'POST') {
    if ($request->request->has('clear')) {
        $session->clear();

        header('Location: ' . $request->server->get('REQUEST_URI'));
    } elseif ($request->request->has('draw')) {
        $draw->execute();

        header('Location: ' . $request->server->get('REQUEST_URI'));
    } elseif ($request->request->has('export')) {
        $draw->exportToCsv(new CSVExporter);
    } elseif ($request->request->has('set_event')) {
        $session->set('event_id', $request->request->get('event_id'));

        header('Location: ' . $request->server->get('REQUEST_URI'));
    }
}

// Get drawn members
$drawnMembers = $draw->getDrawnMembers();
$countRsvpedMembers = $draw->getCountRsvpedMembers();

// Set view
$twigLoader = new \Twig_Loader_Filesystem(__DIR__ . '/views');
$twig = new \Twig_Environment($twigLoader);

if ($eventId === null) {
    echo $twig->render('event.html.twig');
} else {
    echo $twig->render('draw.html.twig', [
        'event_id' => $eventId,
        'drawn_members' => $drawnMembers,
        'count_rsvped_members' => $countRsvpedMembers
    ]);
}
