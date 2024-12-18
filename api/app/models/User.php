<?php

class User {
    protected $id;
    protected $name;
    protected $surname;
    protected $email;

    private static $dataFile = __DIR__ . '/../../storage/users.json'; // Ruta al archivo JSON

    // Constructor
    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['nombre'];
        $this->surname = $data['apellido'];
        $this->email = $data['email'];
    }

    // Metodo para obtener todos los usuarios
    public static function all() {
        $data = json_decode(file_get_contents(self::$dataFile), true) ?: [];
        return array_map(fn($item) => new self($item), $data);
    }

    // Metodo para buscar un usuario por su ID
    public static function find($id) {
        $data = self::all();
        foreach ($data as $user) {
            if ($user->id == $id) {
                return $user;
            }
        }
        return null;
    }

    // Metodo para convertir el objeto a un array
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email
        ];
    }
}