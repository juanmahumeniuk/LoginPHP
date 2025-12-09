<?php
// ==========================================
// LECCIÓN 2: Estructuras de Control (Condicionales)
// ==========================================

// TEORÍA:
// Las estructuras de control nos permiten tomar decisiones en el código.
// La más común es 'if' (si) y 'else' (si no).

echo "--- Inicio del Ejercicio 2 ---\n\n";

$edad = 18;

// Ejemplo Básico
if ($edad >= 18) {
    echo "1. Eres mayor de edad.\n";
} else {
    echo "1. Eres menor de edad.\n";
}

// Operadores de Comparación:
// ==  Igual a
// !=  Diferente de
// >   Mayor que
// <   Menor que
// >=  Mayor o igual que
// <=  Menor o igual que

// Uso de elseif (para múltiples condiciones)
$nota = 85;

if ($nota >= 90) {
    echo "2. Calificación: Excelente\n";
} elseif ($nota >= 70) {
    echo "2. Calificación: Aprobado\n";
} else {
    echo "2. Calificación: Reprobado\n";
}

// Operadores Lógicos
// && (AND) -> Ambas condiciones deben ser verdaderas
// || (OR)  -> Al menos una condición debe ser verdadera

$tengo_dinero = true;
$es_fin_de_semana = false;

if ($tengo_dinero && $es_fin_de_semana) {
    echo "3. ¡Voy al cine!\n";
} else {
    echo "3. Me quedo en casa.\n";
}

// ==========================================
// TAREA PARA TI:
// ==========================================
// 1. Crea una variable $hora con un valor entre 0 y 24.
// 2. Escribe una estructura if/elseif/else que imprima:
//    - "Buenos días" si la hora es menor a 12.
//    - "Buenas tardes" si la hora está entre 12 y 19.
//    - "Buenas noches" si es mayor a 19.
// 3. Prueba cambiando el valor de $hora para ver los diferentes resultados.
// ==========================================

$hora = 12;
if ($hora < 12) {
    echo "Buenos días\n";
} elseif ($hora >= 12 && $hora < 19) {
    echo "Buenas tardes\n";
} else {
    echo "Buenas noches\n";
}


?>