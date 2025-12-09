<?php
// ==========================================
// LECCIÓN 1: Variables y Tipos de Datos
// ==========================================

// TEORÍA:
// 1. Los archivos PHP terminan en .php
// 2. El código PHP debe estar dentro de las etiquetas <?php y ?>
// 3. Las instrucciones terminan con punto y coma (;)
// 4. Las variables siempre empiezan con el signo de dólar ($)

echo "--- Inicio del Ejercicio ---\n\n";

// --- TIPOS DE DATOS BÁSICOS ---

// String (Texto)
$nombre = "Juan";
$curso = 'PHP Básico'; // Se puede usar comillas dobles o simples

// Integer (Número entero)
$edad = 25;

// Float (Decimal)
$precio = 19.99;

// Boolean (Verdadero/Falso)
$es_estudiante = true; // true imprime "1", false no imprime nada

// --- CONCATENACIÓN (Unir textos) ---
// En PHP se usa el punto (.) para unir cadenas

echo "Hola, mi nombre es " . $nombre . ".\n";
echo "Tengo " . $edad . " años.\n";

// Una forma más limpia usando comillas dobles (Interpolación):
echo "Estoy tomando el curso de $curso y cuesta $$precio.\n";

// --- OPERACIONES BÁSICAS ---
$anio_nacimiento = 2024 - $edad;
echo "Nací aproximadamente en el año: $anio_nacimiento\n";

// ==========================================
// TAREA PARA TI:
// ==========================================
// 1. Crea una variable llamada $ciudad con el nombre de tu ciudad.
// 2. Crea una variable $temperatura con un valor numérico.
// 3. Imprime una frase que diga: "Vivo en [ciudad] y hoy hacen [temperatura] grados."
// ==========================================


// Ejercicio 1
$ciudad = "Mendoza";
$temperatura = 22;
echo "Vivo en $ciudad y hoy hacen $temperatura grados.";
?>