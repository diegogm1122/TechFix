<?php

// Definimos los tipos de reparación disponibles como un enum
enum TipoReparacion: string
{
    case Pantalla = 'Pantalla';
    case Bateria = 'Batería';
    case PlacaBase = 'Placa Base';
}

// Clase que representa una orden de trabajo
// readonly significa que sus datos no se pueden modificar una vez creados
readonly class OrdenTrabajo
{
    public function __construct(
        public int $id,
        public string $cliente,
        public TipoReparacion $tipoReparacion,
        public float $manoObra,
        public float $recambios
    ) {
    }
}