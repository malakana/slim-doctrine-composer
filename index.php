<?php

require 'bootstrap.php';

//Ruta para generar un csv desde una tabla de base de datos
$app->get('/home(/)', function () use ($app,$entityManager) {

    $result = $entityManager->getRepository('\Custom\Entity\Test')->findAll();

    $app->render('home.phtml', array(
        'result' => $result
    ));

});

$app->run();
