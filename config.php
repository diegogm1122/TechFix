<?php
//Activamos el tipado estricto de PHP
declare(strict_types=1);

//Mostramos los errores en pantalla y activamos el nivel de error E_ALL
ini_set('display_errors', '1');
error_reporting(E_ALL);

//Establecemos la zona horaria de Madrid
date_default_timezone_set('Europe/Madrid');

//Limpia el texto antes de mostrarlo en pantalla para evitar ataques XSS
function limpiar(string $texto): string
{
    return htmlspecialchars($texto);
}

//Calcula el precio final de un servicio sumando la mano de obra y los recambios, y aplicando el IVA del 21%
function calcularPrecio(float $manoObra, float $recambios): float
{
    $base = $manoObra + $recambios;
    return $base * 1.21;
}