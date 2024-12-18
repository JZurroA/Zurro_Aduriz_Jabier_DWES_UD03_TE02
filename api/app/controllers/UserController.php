<?php

/**
 * Controlador para manejar las operaciones de los usuarios
 */

require_once __DIR__ . '/../models/User.php';

class UserController {

    // Metodo para mostrar todos los usuarios
    public function index() {
        $users = User::all();
        echo json_encode(['users' => $users]);
    }

    // Metodo para mostrar un usuario por su ID
    public function show($id) {
        $user = User::find($id);
        if ($user) {
            echo json_encode(['user' => $user->toArray()]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    }
}