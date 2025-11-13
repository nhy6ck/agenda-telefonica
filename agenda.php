<?php
class Agenda {
    private $contactos = array();

    public function __construct() {
        $this->contactos = array();
    }

    public function registrarContacto($nombre, $numero) {
        global $conn;
        $nombre = $conn->real_escape_string($nombre);
        $numero = $conn->real_escape_string($numero);
        $sql = "INSERT INTO contactos (nombre, numero) VALUES ('$nombre', '$numero')";
        if ($conn->query($sql) === TRUE) {
            return "Contacto registrado exitosamente";
        } else {
            return "Error al registrar el contacto: " . $conn->error;
        }
    }

    public function listarContactos($limit = null, $offset = null) {
        global $conn;
        $sql = "SELECT * FROM contactos";
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $contactos = array();
            while($row = $result->fetch_assoc()) {
                $contacto = new Contacto($row["nombre"], $row["numero"]);
                $contacto->setId($row["id"]);
                $contactos[] = $contacto;
            }
            return $contactos;
        } else {
            return array();
        }
    }

    public function contarContactos() {
        global $conn;
        $sql = "SELECT COUNT(*) as total FROM contactos";
        $result = $conn->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['total'];
        } else {
            return 0;
        }
    }

    public function buscarContacto($busqueda, $limit = null, $offset = null) {
        global $conn;
        $busqueda = $conn->real_escape_string($busqueda);
        $sql = "SELECT * FROM contactos WHERE nombre LIKE '%$busqueda%' OR numero LIKE '%$busqueda%'";
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $contactos = array();
            while ($row = $result->fetch_assoc()) {
                $contacto = new Contacto($row["nombre"], $row["numero"]);
                $contacto->setId($row["id"]);
                $contactos[] = $contacto;
            }
            return $contactos;
        } else {
            return array();
        }
    }

    public function contarContactosBusqueda($busqueda) {
        global $conn;
        $busqueda = $conn->real_escape_string($busqueda);
        $sql = "SELECT COUNT(*) as total FROM contactos WHERE nombre LIKE '%$busqueda%' OR numero LIKE '%$busqueda%'";
        $result = $conn->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['total'];
        } else {
            return 0;
        }
    }

    public function buscarContactoPorId($id) {
        global $conn;
        $sql = "SELECT * FROM contactos WHERE id = $id";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $contacto = new Contacto($row["nombre"], $row["numero"]);
            $contacto->setId($row["id"]);
            return $contacto;
        } else {
            return null;
        }
    }

    public function eliminarContacto($id) {
        global $conn;
        $sql = "DELETE FROM contactos WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            return "Contacto eliminado exitosamente";
        } else {
            return "Error al eliminar el contacto: " . $conn->error;
        }
    }

    public function actualizarContacto($id, $nombre, $numero) {
        global $conn;
        $nombre = $conn->real_escape_string($nombre);
        $numero = $conn->real_escape_string($numero);
        $sql = "UPDATE contactos SET nombre='$nombre', numero='$numero' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            return "Contacto actualizado exitosamente";
        } else {
            return "Error al actualizar el contacto: " . $conn->error;
        }
    }
}
?>