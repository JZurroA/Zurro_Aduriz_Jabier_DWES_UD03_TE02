<?php

/**
 * Clase Reservation: Modelo para manejar las reservas de libros
 */

 class Reservation {
    protected $idReservation;
    protected $idBook;
    protected $idUser;
    protected $startDate;
    protected $endDate;
    protected $available;

    private static $dataFile = __DIR__ . '/../../storage/reservations.json';

    public function __construct($idUser, $idBook, $startDate, $endDate, $id = null) {
        $this->idReservation = $id ?? $this->generateId();
        $this->idUser = $idUser;
        $this->idBook = $idBook;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }


    // Getters y Setters
    public function getIdBook() {
        return $this->idBook;
    }
    
    public function getIdReservation() {
        return $this->idReservation;
    }   

    public function getStartDate() {
        return $this->startDate;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function setAvailability($status) {
        $this->available = $status;
    }

    public function setStartDate($date) {
        $this->startDate = $date;
    }

    public function setEndDate($date) {
        $this->endDate = $date;
    }

    // Metodo para generar una ID de reserva de manera consecutiva.
    private function generateId() {
        $data = self::all();
        return $data ? end($data)->idReservation + 1 : 1;
    }

    // Metodo para actualizar la disponibilidad de la reserva en caso de poder reservar.
    public function updateAvailability() {
        // Sincronizar el estado de disponibilidad después de guardar
        $today = new DateTime('now');
        $resStartDate = new DateTime($this->getStartDate());
        $resEndDate = new DateTime($this->getEndDate());
    
        if ($today >= $resStartDate && $today <= $resEndDate) {
            $this->setAvailability(false); // Reserva activa
        } elseif ($today > $resEndDate) {
            $this->setAvailability(true); // Reserva pasada
        } else {
            $this->setAvailability(false); // Reserva futura
        }
    }

    // Metodo para obtener todas las reservas.
    public static function all() {
        $data = json_decode(file_get_contents(self::$dataFile), true) ?: [];
        return array_map(fn($item) => new self($item['idUser'], $item['idBook'], $item['startDate'], $item['endDate'], $item['idReservation']), $data);
    }

    // Metodo para buscar una reserva por ID.
    public static function find($id) {
        $data = self::all();
        foreach ($data as $reservation) {
            if ($reservation->idReservation == $id) {
                return $reservation;
            }
        }
        return null;
    } 

    // Metodo para guardar una reserva.
    public function save() {
        // Cargar todas las reservas como objetos
        $data = self::all();
        $exists = false;
    
        // Verificar si la reserva ya existe y actualizarla
        foreach ($data as $index => $reservation) {
            if ($reservation->getIdReservation() == $this->getIdReservation()) {
                // Si la reserva existe, reemplazarla por la nueva
                $data[$index] = $this; 
                $exists = true;
                break;
            }
        }
    
        // Si la reserva no existe, agregarla al array
        if (!$exists) {
            $data[] = $this; // Aniadir la nueva reserva al final
        }
    
        // Asegurarse de que solo las reservas que deben actualizarse tienen 'available' correctamente
        foreach ($data as $reservation) {
            $reservation->updateAvailability(); // Actualizar la disponibilidad de cada reserva
        }
    
        // Guardar los cambios en el archivo JSON
        file_put_contents(self::$dataFile, json_encode(array_map(fn($r) => $r->toArray(), $data), JSON_PRETTY_PRINT));
    }      
    
    // Metodo para actualizar una reserva.
    public function update($newData) {
        foreach ($newData as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->save();
    }

    // Metodo para eliminar una reserva.
    public function delete() {
        $data = self::all();
        $filtered = array_filter($data, fn($r) => $r->idReservation != $this->idReservation);
        file_put_contents(
            self::$dataFile,
            json_encode(array_map(fn($item) => $item->toArray(), $filtered), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }    

    // Metodo para verificar si un libro está disponible.
    public static function isBookAvailable($idBook, $startDate, $endDate, $excludeId = null) {
        $reservations = self::all();
    
        // Convertir las fechas de entrada a objetos DateTime
        $startDateObj = new DateTime($startDate);
        $endDateObj = new DateTime($endDate);
    
        foreach ($reservations as $reservation) {
            // Validar fechas de la reserva
            if (!$reservation->startDate || !$reservation->endDate) {
                continue;
            }
    
            // Ignorar la reserva actual
            if ($reservation->idReservation == $excludeId) {
                continue;
            }
    
            // Validar solo reservas para el mismo libro
            if ($reservation->idBook != $idBook) {;
                continue;
            }
    
            // Convertir fechas de la reserva a objetos DateTime
            $resStartDate = new DateTime($reservation->startDate);
            $resEndDate = new DateTime($reservation->endDate);
    
            // Evaluar si hay solapamiento
            if ($startDateObj <= $resEndDate && $endDateObj >= $resStartDate) {
                return false;
            }
        }
        return true;
    }                       

    // Metodo para convertir la reserva en un array.
    public function toArray() {
        return [
            'idReservation' => $this->idReservation,
            'idBook' => $this->idBook,
            'idUser' => $this->idUser,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'available' => $this->available
        ];
    }
}