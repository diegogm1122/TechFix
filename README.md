# TechFix

TechFix - Gestión de Reparaciones

## Descripción

TechFix es una aplicación desarrollada en PHP para gestionar solicitudes de reparación de dispositivos y un pequeño catálogo de recambios.

El proyecto permite:

Recibir una solicitud mediante parámetros de la URL.
Identificar al cliente.
Validar los datos recibidos.
Crear una orden de trabajo.
Calcular el presupuesto con un 21 % de IVA.
Gestionar un catálogo de recambios.
Aplicar un recargo del 15 % a los precios.
Filtrar los recambios disponibles según su stock.
Calcular el valor total del almacén.
Mostrar los recambios mediante un sistema de paginación.
Generar una vista HTML de forma segura.

## ¿Cómo funciona?

El funcionamiento del proyecto sigue varios pasos.

## 1. Recepción de datos

El proyecto recibe los datos mediante parámetros GET en la URL.

Por ejemplo:

http://localhost:8000/?solicitud=1&cliente=Diego&pagina=1

Los parámetros utilizados son:

solicitud: número de la solicitud de reparación.
cliente: nombre del cliente.
pagina: página del catálogo de recambios.
## 2. Validación de la solicitud

El número de solicitud se comprueba para asegurarnos de que sea un número entero mayor que 0.

Si el dato no es válido, el servidor devuelve un error HTTP 400 y detiene la ejecución.

## 3. Tratamiento del cliente

Si no se proporciona un cliente, se utiliza:

Cliente Anónimo

El nombre se procesa utilizando:

trim() para eliminar espacios.
mb_strtoupper() para convertirlo a mayúsculas.
mb_strlen() para obtener su longitud correctamente.

## 4. Creación de la orden de trabajo

Se crea un objeto de la clase OrdenTrabajo.

La orden contiene:

ID de la solicitud.
Nombre del cliente.
Tipo de reparación.
Coste de mano de obra.
Coste de los recambios.

El tipo de reparación se define mediante un enum.

Los tipos disponibles son:

Pantalla
Batería
Placa Base

Además, se utiliza match para asociar cada tipo con una descripción.

## 5. Cálculo del presupuesto

El presupuesto se calcula mediante la función:

calcularPrecio()

La función recibe el coste de la mano de obra y de los recambios y aplica un 21 % de IVA.

Por ejemplo:

Mano de obra: 50 €
Recambios: 100 €

Base: 150 €
IVA (21 %): 31,50 €

Total: 181,50 €

## 6. Gestión del catálogo

El proyecto contiene un catálogo de recambios.

Cada recambio tiene:

Nombre
Precio
Stock

Antes de mostrar el catálogo, se aplica un recargo del 15 % sobre el precio de cada pieza.

El catálogo se modifica utilizando referencias en un foreach.

Después se utiliza:

unset($pieza);

para eliminar la referencia.

## 7. Filtrado del stock

Se utiliza array_filter() para mostrar solamente los recambios que tienen unidades disponibles.

Los productos con:

stock = 0

no se muestran en el catálogo disponible.

## 8. Valor total del almacén

El valor total del almacén se calcula multiplicando:

precio × stock

para cada recambio disponible.

Después se suman todos los resultados.

## 9. Paginación

El catálogo utiliza paginación para mostrar 2 recambios por página.

El usuario puede utilizar los botones:

Anterior
Siguiente

El programa controla que no se pueda acceder a una página inferior a la primera ni superior a la última.

## 10. Generación de la vista

El HTML se genera utilizando un búfer de salida.

Se inicia con:

ob_start();

y posteriormente se recupera el contenido con:

ob_get_clean();

Finalmente, el contenido HTML se muestra mediante echo.

## 11. Seguridad

Los datos recibidos del usuario se limpian antes de mostrarlos en HTML.

Para ello se utiliza:

htmlspecialchars()

a través de la función:

limpiar()

Esto permite neutralizar caracteres especiales y proteger la aplicación frente a ataques XSS.

## Estructura del proyecto

La estructura principal del proyecto es:

TechFix/
│
├── index.php
├── config.php
├── OrdenTrabajo.php
└── README.md

## Tecnologías utilizadas

PHP
HTML5
Git
GitHub

## Autor

Proyecto realizado como práctica de desarrollo Back-End con PHP por Diego Garrido Martinez

