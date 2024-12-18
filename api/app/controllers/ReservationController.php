<?php

/**
 * Controlador para manejar las operaciones de las reservas
 */

require_once __DIR__ . '/../models/Reservation.php';

class ReservationController {

    // Metodo para controlar la creación de una reserva
    public function store() {
        $input = json_decode(file_get_contents('php://input'), true);
        
             // Verificar que los datos necesarios están presentes
        if (empty($input['startDate']) || empty($input['endDate'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Reservation starting date and ending date are required']);
            return;
        }

        // Validar el formato de las fechas
        if (!strtotime($input['startDate']) || !strtotime($input['endDate'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid date format']);
            return;
        }

        // Comprobar que la fecha de inicio no sea anterior a la fecha actual
        $startDate = new DateTime($input['startDate']);
        $today = new DateTime('now');

        if ($startDate < $today) {
            http_response_code(400);
            echo json_encode(['error' => 'Start date cannot be in the past']);
            return;
        }

        // Verificar disponibilidad del libro
        if (!Reservation::isBookAvailable($input['idBook'], $input['startDate'], $input['endDate'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Book is already reserved for the requested dates']);
            return;
        }
    
        // Crear la nueva reserva
        $reservation = new Reservation($input['idUser'], $input['idBook'], $input['startDate'], $input['endDate']);
        $reservation->updateAvailability();
        $reservation->save();
    
        // Responder con el estado sincronizado
        http_response_code(201);
        echo json_encode([
            'message' => 'Reservation created',
            'reservation' => $reservation->toArray() // Objeto sincronizado
        ]);
    }                
    
    // Metodo para controlar la actualizacion de una reserva
    public function update($id) {
        // Obtener los datos de la solicitud
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Verificar que los datos necesarios estan presentes
        if (empty($input['startDate']) || empty($input['endDate'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Reservation starting date and ending date are required']);
            return;
        }
    
        // Validar el formato de las fechas
        if (!strtotime($input['startDate']) || !strtotime($input['endDate'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid date format']);
            return;
        }
    
        // Buscar la reserva por ID
        $reservation = Reservation::find($id);
        if ($reservation) {
            // Comprobar si el libro esta disponible para las nuevas fechas
            if (!Reservation::isBookAvailable($reservation->getIdBook(), $input['startDate'], $input['endDate'], $id)) {
                http_response_code(400);
                echo json_encode(['error' => 'The book is already reserved for the requested dates']);
                return;
            }
    
            // Actualizar las fechas de la reserva
            $reservation->setStartDate($input['startDate']);
            $reservation->setEndDate($input['endDate']);
            $reservation->updateAvailability();

            // Guardar los cambios
            $reservation->save();
    
            // Responder con éxito
            http_response_code(200);
            echo json_encode([
                'message' => "Reservation $id updated",
                'reservation' => $reservation->toArray()
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Reservation not found']);
        }
    }
          

    // Metodo para controlar la eliminacion de una reserva
    public function destroy($id) {
        $reservation = Reservation::find($id);
        if ($reservation) {
            $reservation->delete();
            
            // Actualizar disponibilidad del libro al eliminar la reserva
            $this->updateBookAvailability($reservation->getIdBook(), true);

            http_response_code(200);
            echo json_encode(['message' => "Reservation with ID " . $id . " deleted"]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Reservation not found']);
        }
    }    

    // Metodo para controlar la actualizacion de la disponibilidad de un libro
    private function updateBookAvailability($idBook, $status) {
        $reservations = Reservation::all();
        foreach ($reservations as $reservation) {
            if ($reservation->getIdBook() == $idBook) {
                $reservation->setAvailability($status);
                $reservation->save(); // Uso del método save() de Reservation
                break;
            }
        }
    }
}