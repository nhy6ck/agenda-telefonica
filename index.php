<?php
include 'config/db_connection.php';
include 'contacto.php';
include 'agenda.php';

$conn = OpenCon();
$agenda = new Agenda();

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$searchName = isset($_GET['search-name']) ? trim($_GET['search-name']) : '';
$hayBusqueda = !empty($searchName);

function validarNombre($nombre) {
    return preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u", $nombre);
}

function validarTelefono($telefono) {
    return preg_match("/^\+?\d+(\s\d+)*$/", $telefono);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mostrar_formulario'])) {
        $mostrarFormulario = true;
    } elseif ((isset($_POST['name']) && isset($_POST['phone'])) && (!empty($_POST['name']) && !empty($_POST['phone']))) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        
        if (!validarNombre($name)) {
            $result = "El nombre no debe contener caracteres especiales ni números";
            $mostrarFormulario = true;
        } elseif (!validarTelefono($phone)) {
            $result = "El número de teléfono solo debe contener números";
            $mostrarFormulario = true;
        } else {
            if (isset($_POST['update-id'])) {
                $id = (int)$_POST['update-id'];
                $result = $agenda->actualizarContacto($id, $name, $phone);
            } else {
                $result = $agenda->registrarContacto($name, $phone);
            }
            $mostrarFormulario = false;
        }
    } elseif (isset($_POST['delete-id'])) {
        $deleteId = $_POST['delete-id'];
        $result = $agenda->eliminarContacto($deleteId);
    } elseif (isset($_POST['edit-id'])) {
        $editId = (int)$_POST['edit-id'];
        $contactoEditar = $agenda->buscarContactoPorId($editId);
    }
}

if ($hayBusqueda) {
    $totalContactos = $agenda->contarContactosBusqueda($searchName);
    $contactos = $agenda->buscarContacto($searchName, $limit, $offset);
} else {
    $totalContactos = $agenda->contarContactos();
    $contactos = $agenda->listarContactos($limit, $offset);
}

$totalPages = max(1, ceil($totalContactos / $limit));

$paginaValida = ($page >= 1 && $page <= $totalPages);

CloseCon($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agenda Telefónica</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
<div class="contenedor">
    <h1><a href="index.php" class="titulo">Agenda Telefónica</a></h1>

    <?php if (!$paginaValida): ?>
        <form method="POST" action="">
            <button type="submit" name="mostrar_formulario">Registrar Contacto</button>
        </form>
        <p class="mensaje">No se encontró la página solicitada</p>
    <?php else: ?>

        <?php if (isset($contactoEditar) && $contactoEditar !== null): ?>
            <h2>Editar Contacto</h2>
            <form method="POST" action="">
                <input type="hidden" name="update-id" value="<?php echo $contactoEditar->getId(); ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($contactoEditar->getNombre()); ?>" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" title="Solo letras" required>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($contactoEditar->getNumero()); ?>" pattern="^\+?\d+(\s\d+)*$" title="Solo números" required>
                <div class="contenedor-boton-editar">
                    <button type="submit">Actualizar Contacto</button>
                    <a href="index.php" class="boton-cancelar" role="button">Cancelar</a>
                </div>
            </form>

        <?php else: ?>
            <?php if (isset($mostrarFormulario) && $mostrarFormulario): ?>
                <h2>Registrar Contacto</h2>
                <form method="POST" action="">
                    <input type="text" name="name" placeholder="Nombre" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" title="Solo letras" required>
                    <input type="text" name="phone" placeholder="Número de teléfono" pattern="^\+?\d+(\s\d+)*$" title="Solo números" required>
                    <div class="contenedor-boton-registro">
                        <button type="submit">Registrar Contacto</button>
                        <a href="index.php" class="boton-cancelar" role="button">Cancelar</a>
                    </div>
                </form>
            <?php else: ?>
                <form method="POST" action="">
                    <button type="submit" name="mostrar_formulario">Registrar Contacto</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($result)): ?>
            <p class="mensaje"><?php echo htmlspecialchars($result); ?></p>
        <?php endif; ?>

        <?php if (!isset($contactoEditar) && !(isset($mostrarFormulario) && $mostrarFormulario)): ?>
            <div class="busqueda">
                <form method="GET" action="">
                    <div class="contenedor-busqueda">
                        <input type="text" name="search-name" placeholder="Buscar contacto por nombre o teléfono" value="<?php echo htmlspecialchars($searchName); ?>" required>
                        <button type="submit" class="boton-buscar">Buscar</button>
                    </div>
                </form>

                <?php if ($hayBusqueda): ?>
                    <?php if (is_array($contactos) && count($contactos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Número</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contactos as $contacto): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($contacto->getNombre()); ?></td>
                                            <td><?php echo htmlspecialchars($contacto->getNumero()); ?></td>
                                            <td class="acciones">
                                                <form method="POST" action="" style="display:inline-block;">
                                                    <input type="hidden" name="edit-id" value="<?php echo $contacto->getId(); ?>">
                                                    <button type="submit" class="boton-editar" title="Editar Contacto">Editar</button>
                                                </form>
                                                <form method="POST" action="" style="display:inline-block; margin-left:5px;">
                                                    <input type="hidden" name="delete-id" value="<?php echo $contacto->getId(); ?>">
                                                    <button class="boton-eliminar" type="submit" title="Eliminar Contacto" onclick="return confirm('¿Estás seguro que deseas eliminar a este contacto?')">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="paginacion">
                            <?php if ($page > 1): ?>
                                <a href="?page=1&search-name=<?php echo urlencode($searchName); ?>" title="Primera página">&laquo; Primera</a>
                            <?php endif; ?>

                            <?php
                            $delta = 2;
                            $pages = [];

                            $pages[] = 1;

                            $left = max($page - $delta, 2);
                            $right = min($page + $delta, $totalPages - 1);

                            for ($i = $left; $i <= $right; $i++) {
                                $pages[] = $i;
                            }

                            if ($totalPages > 1) {
                                $pages[] = $totalPages;
                            }

                            $pages = array_unique($pages);
                            sort($pages);

                            $lastPage = 0;
                            foreach ($pages as $pg) {
                                if ($lastPage != 0 && $pg > $lastPage + 1) {
                                    echo '<span class="puntos">...</span>';
                                }
                                $url = "?page=$pg&search-name=" . urlencode($searchName);
                                if ($pg == $page) {
                                    echo '<a href="' . $url . '" class="active">' . $pg . '</a>';
                                } else {
                                    echo '<a href="' . $url . '">' . $pg . '</a>';
                                }
                                $lastPage = $pg;
                            }
                            ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $totalPages; ?>&search-name=<?php echo urlencode($searchName); ?>" title="Última página">Última &raquo;</a>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <p class="mensaje">No se encontraron contactos</p>
                    <?php endif; ?>

                <?php else: ?>

                    <?php if (is_array($contactos) && count($contactos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Número</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contactos as $contacto): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($contacto->getNombre()); ?></td>
                                            <td><?php echo htmlspecialchars($contacto->getNumero()); ?></td>
                                            <td class="acciones">
                                                <form method="POST" action="" style="display:inline-block;">
                                                    <input type="hidden" name="edit-id" value="<?php echo $contacto->getId(); ?>">
                                                    <button type="submit" class="boton-editar" title="Editar Contacto">Editar</button>
                                                </form>
                                                <form method="POST" action="" style="display:inline-block; margin-left:5px;">
                                                    <input type="hidden" name="delete-id" value="<?php echo $contacto->getId(); ?>">
                                                    <button class="boton-eliminar" type="submit" title="Eliminar Contacto" onclick='return confirm("¿Estás seguro que deseas eliminar a este contacto?")'>Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="paginacion">
                            <?php if ($page > 1): ?>
                                <a href="?page=1" title="Primera página">&laquo; Primera</a>
                            <?php endif; ?>

                            <?php
                            $delta = 2;
                            $pages = [];

                            $pages[] = 1;

                            $left = max($page - $delta, 2);
                            $right = min($page + $delta, $totalPages - 1);

                            for ($i = $left; $i <= $right; $i++) {
                                $pages[] = $i;
                            }

                            if ($totalPages > 1) {
                                $pages[] = $totalPages;
                            }

                            $pages = array_unique($pages);
                            sort($pages);

                            $lastPage = 0;
                            foreach ($pages as $pg) {
                                if ($lastPage != 0 && $pg > $lastPage + 1) {
                                    echo '<span class="puntos">...</span>';
                                }
                                $url = "?page=$pg";
                                if ($pg == $page) {
                                    echo '<a href="' . $url . '" class="active">' . $pg . '</a>';
                                } else {
                                    echo '<a href="' . $url . '">' . $pg . '</a>';
                                }
                                $lastPage = $pg;
                            }
                            ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $totalPages; ?>" title="Última página">Última &raquo;</a>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <p class="mensaje">No hay contactos registrados</p>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
</body>
</html>