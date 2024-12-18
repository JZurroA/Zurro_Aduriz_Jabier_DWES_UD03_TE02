<?php

/**
 * Clase Book: Modelo para manejar los libros
 */

 class Book {
    protected $id;
    protected $title;
    protected $author;
    protected $genre;

    private static $dataFile = __DIR__ . '/../../storage/books.json';

    // Constructor
    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['titulo'] ?? 'Unknown Title'; // Mapeo 'titulo' -> 'title'
        $this->author = $data['autor'] ?? 'Unknown Author'; // Mapeo 'autor' -> 'author'
        $this->genre = $data['genero'] ?? 'Unknown Genre'; // Mapeo 'genero' -> 'genre'
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getGenre() {
        return $this->genre;
    }

    public function getAuthor() {
        return $this->author;
    }    

    // Metodo para obtener todos los libros
    public static function all() {
        $data = json_decode(file_get_contents(self::$dataFile), true) ?: [];
        $instances = array_map(fn($item) => new self($item), $data);
        return $instances;
    }   

    // Metodo para buscar un libro por su ID
    public static function find($id) {
        $data = self::all();
        foreach ($data as $book) {
            if ($book->id == $id) {
                return $book;
            }
        }
        return null;
    }

    // Metodo para convertir el objeto a un array
    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'genre' => $this->genre
        ];
    }   
}