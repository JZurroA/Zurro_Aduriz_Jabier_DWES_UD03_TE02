<?php

/**
 * Controlador para manejar las operaciones de los libros
 */

require_once __DIR__ . '/../models/Book.php';

class BookController {

    // Metodo para mostrar todos los libros
    public function index() {
        $books = Book::all(); // Obtiene las instancias de Book
        $booksArray = array_map(fn($book) => $book->toArray(), $books); // Convierte a array
        header('Content-Type: application/json'); // Establece el encabezado
        http_response_code(200);
        echo json_encode(['books' => $booksArray], JSON_PRETTY_PRINT); // Solo esta salida
    }       

    // Metodo para mostrar un libro por su ID
    public function show($id) {
        $book = Book::find($id);
        if ($book) {
            http_response_code(200);
            echo json_encode(['book' => $book->toArray()]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found']);
        }
    }
}