<?php

// Cargamos el archivo de configuración
require_once 'config.php';

// Cargamos la clase OrdenTrabajo y el enum
require_once 'OrdenTrabajo.php';

/* VALIDACIÓN DE LA SOLICITUD */

//Comprobamos si existe el parámetro "solicitud"
// Si no existe, ponemos 0
$solicitud = isset($_GET['solicitud']) ? $_GET['solicitud'] : 0;

//Comprobamos que sea un numero entero mayor que 0
$solicitud = filter_var($solicitud, FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 1
    ]
]);

// Si la solicitud no es válida, mostramos un mensaje de error
if ($solicitud === false) {
    
    // Indicamos que la solicitud es incorrecta
    http_response_code(400);

    // Detenemos el programa y mostramos un mensaje de error
    exit('Error 400: la solicitud debe ser un número entero mayor que 0.');
}

/* DATOS DEL CLIENTE */

// Obtenemos el nombre del cliente
// Si no existe, utilizamos "Cliente Anonimo"
$cliente = isset($_GET['cliente']) ? $_GET['cliente'] : 0;

// Eliminamos espacios al principio y al final
$cliente = trim($cliente);

// Convertimos el nombre a mayúsculas
$cliente = mb_strtoupper($cliente);

// Contamos los caracteres del nombre
$longitud = mb_strlen($cliente);

/* CREAR LA ORDEN */

// Creamos una nueva orden de trabajo
$orden = new OrdenTrabajo(
    id: $solicitud,
    cliente: $cliente,
    tipoReparacion: TipoReparacion::Pantalla,
    manoObra: 50.0,
    recambios: 100.0
);

/* CALCULAR PRESUPUESTO */

// Calculamos el precio final de la reparación
$total = calcularPrecio($orden->manoObra, $orden->recambios);

/* CATÁLOGO DE RECAMBIOS */

//Creamos el catálogo de piezas
$recambios = [
    ['nombre' => 'Pantalla', 'precio' => 100.0],
    ['nombre' => 'Batería', 'precio' => 50.0],
    ['nombre' => 'Placa Base', 'precio' => 200.0]
];

/* AUMENTAR PRECIOS */

// Recorremos el catálogo usando un foreach
foreach ($recambios as &$pieza) {

    // Aumentamos el precio en un 15%
    $pieza['precio'] = $pieza['precio'] * 1.15;
}

// Eliminamos la referencia para evitar efectos secundarios
unset($pieza);

/* FILTRAR RECAMBIOS */

// Nos quedamos solamente con las piezas que tienen unidades disponible
$disponibles = array_filter($recambios, function ($pieza) {
    return $pieza['stock'] > 0;
});

/* VALOR TOTAL DEL ALMACEN */

// Variable para guardar el valor total
$valorTotal = 0;

// Recorremos las piezas disponibles
foreach ($disponibles as $pieza) {
    // Multiplicamos precio por unidades
    $valorTotal += $pieza['precio'] * $pieza['stock'];
}

