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
$cliente = isset($_GET['cliente']) ? $_GET['cliente'] : 'Cliente Anónimo';

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

/* Descripción del tipo */

$descripcionTipo = match ($orden->tipoReparacion) {

    TipoReparacion::Pantalla =>
        'Reparación o sustitución de pantalla',

    TipoReparacion::Bateria =>
        'Cambio de batería',

    TipoReparacion::PlacaBase =>
        'Reparación de placa base'
};

/* CALCULAR PRESUPUESTO */

// Calculamos el precio final de la reparación utilizando argumentos nombrados
$total = calcularPrecio(manoObra: $orden->manoObra, recambios: $orden->recambios);

/* CATÁLOGO DE RECAMBIOS */

//Creamos el catálogo de piezas
$recambios = [
    ['nombre' => 'Pantalla', 'precio' => 100.0, 'stock' => 10],
    ['nombre' => 'Batería', 'precio' => 50.0, 'stock' => 20],
    ['nombre' => 'Placa Base', 'precio' => 200.0, 'stock' => 5]
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

/* PAGINACIÓN */

//Número de piezas que mostramos por página
$porPagina = 2;

//Obtenemos el número de página
// Si no existe, utilizamos la pagina 1
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

// Comprobamos que sea un numero entero
$pagina = filter_var($pagina, FILTER_VALIDATE_INT);

// Si no es válida, ponemos la página 1
if ($pagina === false || $pagina < 1) {
    $pagina = 1;
}

// Calculamos el numero total de páginas
$totalPaginas = (int) ceil(count($disponibles) / $porPagina);

// Como mínimo debe existir una página
if ($totalPaginas < 1) {
    $totalPaginas = 1;
}

// Si se pide una página superior a la última, mostramos la última página
if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}

// Calculamos desde qué posición debemos empezar
$inicio = ($pagina - 1) * $porPagina;

// Obtenemos las piezas que corresponden a esta página
$lista = array_slice($disponibles, $inicio, $porPagina);

/* BÚFER DE SALIDA */

// Empezamos a guardar el HTML en memoria
ob_start();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>TechFix</title>
</head>

<body>
    <h1>TechFix - Gestión de Reparaciones</h1>

    <h2>Datos del cliente</h2>

    <!-- Mostramos los datos de la solicitud -->
    <p>
        Solicitud:
        <?= limpiar((string) $orden->id) ?>
    </p>

    <!-- Mostramos el nombre del cliente -->
    <p>
        Cliente:
        <?= limpiar($orden->cliente) ?>
    </p>

    <!-- Mostramos la longitud del nombre del cliente -->
    <p>
        Longitud del nombre:
        <?= $longitud ?>
    </p>

    <!-- Mostramos el tipo de reparación -->
    <p>
        Tipo de reparación:
        <?= limpiar($orden->tipoReparacion->value) ?>
    </p>

    <h2>Presupuesto</h2>

    <!-- Mostramos el precio de la mano de obra -->
    <p>
        Mano de obra:
        <?= $orden->manoObra ?> €
    </p>

    <!-- Mostramos el precio de los recambios -->
    <p>
        Recambios:
        <?= $orden->recambios ?> €
    </p>

    <!-- Mostramos el precio total con el IVA incluido -->
    <p>
        Total (IVA incluido):
        <?= number_format($total, 2) ?> €
    </p>

    <h2>Catálogo de recambios</h2>

    <table border="1">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <!-- Recorremos las piezas de la página actual -->
            <?php foreach ($lista as $pieza) : ?>
                <tr>
                    <!-- Nombre de la pieza -->
                    <td><?= limpiar($pieza['nombre']) ?></td>

                    <!-- Precio de la pieza -->
                    <td><?= number_format($pieza['precio'], 2) ?> €</td>

                    <!-- Stock de la pieza -->
                    <td><?= $pieza['stock'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Mostramos el valor total del almacén -->
    <p>
        Valor total del almacén:
        <?= number_format($valorTotal, 2) ?> €
    </p>

    <!-- Mostramos la página actual -->
    <p>
        Página <?= $pagina ?> de <?= $totalPaginas ?>
    </p>

    <!-- Botón para ir a la página anterior -->
    <?php if ($pagina > 1) : ?>
        <a href="?solicitud=<?= $orden->id ?>&cliente=<?= urlencode($cliente) ?>&pagina=<?= $pagina - 1 ?>">Anterior</a>
    <?php endif; ?>

    <!-- Botón para ir a la página siguiente -->
    <?php if ($pagina < $totalPaginas) : ?>
        <a href="?solicitud=<?= $orden->id ?>&cliente=<?= urlencode($cliente) ?>&pagina=<?= $pagina + 1 ?>">Siguiente</a>
    <?php endif; ?>

</body>
</html>

<?php

// Recuperamos todo el HTML que estaba en el búfer
$html = ob_get_clean();

// Mostramos el HTML en el navegador
echo $html;