<?php
// LECCIÓN 3: Cheat Sheet de Sintaxis (Arrays y Funciones)
// Para desarrolladores que vienen de JS/Python/Java

echo "--- Arrays y Funciones ---\n";

// 1. ARRAYS (Similar a Listas en Python / Arrays en JS)
// ====================================================
$colores = ["Rojo", "Verde", "Azul"]; // Sintaxis corta (moderna)
// $colores = array("Rojo", "Verde"); // Sintaxis antigua

echo "Primer color: " . $colores[0] . "\n";
$colores[] = "Amarillo"; // Push (agregar al final)

// Recorrer Array (foreach es clave en PHP)
echo "Colores: ";
foreach ($colores as $color) {
    echo "$color ";
}
echo "\n\n";

// 2. ARRAYS ASOCIATIVOS (Similar a Diccionarios/Objetos/Mapas)
// ============================================================
// Clave => Valor
$usuario = [
    "nombre" => "Ana",
    "email" => "ana@test.com",
    "activo" => true
];

echo "Email de usuario: " . $usuario["email"] . "\n";

// Recorrer clavel/valor
foreach ($usuario as $key => $value) {
    // Manejo de booleano para impresión
    if (is_bool($value))
        $value = $value ? 'true' : 'false';
    echo "$key: $value\n";
}
echo "\n";

// 3. FUNCIONES
// ============
function saludar($nombre, $formal = false)
{ // Parámetro opcional
    if ($formal) {
        return "Buenas noches, Sr/Sra $nombre";
    }
    return "Hola $nombre";
}

echo saludar("Carlos") . "\n";
echo saludar("Carlos", true) . "\n";

// Arrow Functions (fn) - Similares a JS, útiles para callbacks
$numeros = [1, 2, 3, 4, 5];
$cuadrados = array_map(fn($n) => $n * $n, $numeros);

print_r($cuadrados); // print_r imprime estructuras complejas para debug

// ==========================================
// MINI-RETO (Sintaxis):
// ==========================================
// 1. Crea un array asociativo que represente un 'Producto' (nombre, precio, stock).
// 2. Crea una función 'mostrarProducto' que reciba ese array y lo imprima bonito.
// 3. Llama a la función.
// ==========================================

$Producto = [
    "nombre" => "Producto 1",
    "precio" => 10,
    "stock" => 10
];

function mostrarProducto($producto)
{
    echo "Nombre: " . $producto["nombre"] . "\n";
    echo "Precio: " . $producto["precio"] . "\n";
    echo "Stock: " . $producto["stock"] . "\n";
}
mostrarProducto($Producto);
?>