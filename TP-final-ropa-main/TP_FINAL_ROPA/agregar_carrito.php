<?php
session_start();
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $cantidad = $_POST['cantidad'];

    $query = "SELECT * FROM productos WHERE id = $id";
    $result = mysqli_query($conexion, $query);
    $producto = mysqli_fetch_assoc($result);

    if ($producto) {
        $item = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'cantidad' => $cantidad,
            'imagen' => $producto['imagen']
        ];

        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // Si el producto ya está en el carrito, sumamos cantidad
        $existe = false;
        foreach ($_SESSION['carrito'] as &$prod) {
            if ($prod['id'] == $id) {
                $prod['cantidad'] += $cantidad;
                $existe = true;
                break;
            }
        }

        if (!$existe) {
            $_SESSION['carrito'][] = $item;
        }

        echo "OK";
    } else {
        echo "Error: producto no encontrado.";
    }
}
?>
