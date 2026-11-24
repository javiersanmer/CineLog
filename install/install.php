<?php
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

$configDir = dirname(__DIR__) . '/config';
$configFile = $configDir . '/config.php';
$mensajes = [];
$error = false;

if (isset($_POST['install'])) {
    // 1. Obtener datos de conexión
    if (file_exists($configFile)) {
        include_once($configFile);
        $host   = DB_HOST;
        $user   = DB_USER;
        $passDB = DB_PASS;
        $nameDB = DB_NAME;
        $portDB = DB_PORT;
    } else {
        $host   = trim($_POST['host']);
        $user   = trim($_POST['user']);
        $passDB = $_POST['passDB'];
        $nameDB = trim($_POST['nameDB']);
        $portDB = (int)$_POST['portDB'];
    }

    // 2. Conectar al servidor MySQL
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conexion = new mysqli($host, $user, $passDB, '', $portDB);
        $conexion->set_charset("utf8mb4");
    } catch (Exception $e) {
        $conexion = false;
        $error = true;
        $mensajes[] = "Error al conectar con el servidor MySQL: " . $e->getMessage();
    }

    if ($conexion !== false) {
        try {
            // Crear la base de datos si no existe y seleccionarla
            $nameDBEscaped = $conexion->real_escape_string($nameDB);
            $conexion->query("CREATE DATABASE IF NOT EXISTS `$nameDBEscaped` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conexion->select_db($nameDB);
            $mensajes[] = "Base de datos <strong>$nameDB</strong> verificada/creada con éxito.";

            // Calcular RUTA_APP
            $ruta = $_SERVER['REQUEST_URI'];
            $ruta = explode('/', $ruta);
            array_pop($ruta);
            array_pop($ruta);
            $ruta = implode('/', $ruta);

            // Generar contenido de config.php
            $contenidoArchivoConfig = "<?php\n"
                . "define('DB_HOST', '" . addslashes($host) . "');\n"
                . "define('DB_USER', '" . addslashes($user) . "');\n"
                . "define('DB_PASS', '" . addslashes($passDB) . "');\n"
                . "define('DB_NAME', '" . addslashes($nameDB) . "');\n"
                . "define('DB_PORT', '$portDB');\n"
                . "define('RUTA_APP', '$ruta');\n"
                . "?>";

            // Crear carpeta config si no existe
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }

            $fp = @fopen($configFile, "w");
            if ($fp === false) {
                $error = true;
                $mensajes[] = "Error al crear <code>config/config.php</code>. Verifica los permisos de escritura. Puedes crearlo manualmente con:<br><pre>" . htmlspecialchars($contenidoArchivoConfig) . "</pre>";
            } else {
                fwrite($fp, $contenidoArchivoConfig);
                fclose($fp);
                $mensajes[] = "Archivo de configuración creado con éxito.";

                // Recoger y sanear datos del administrador
                $loginAdmin    = $conexion->real_escape_string(trim($_POST['login']));
                $nameAdmin     = $conexion->real_escape_string(trim($_POST['name']));
                $surname1Admin = $conexion->real_escape_string(trim($_POST['surname1']));
                $surname2Admin = !empty(trim($_POST['surname2'])) ? "'" . $conexion->real_escape_string(trim($_POST['surname2'])) . "'" : "NULL";
                $mailAdmin     = $conexion->real_escape_string(trim($_POST['mail']));
                $passAdmin     = password_hash($_POST['pass'], PASSWORD_DEFAULT);

                // Incluir las consultas de creación de tablas de CineLog
                include_once('consultas.php');

                // Añadir inserción del primer administrador (usando 'active' en lugar de 'enabled')
                $consultas[] = "INSERT INTO `users` (`login`, `name`, `surname1`, `surname2`, `email`, `foto_perfil`, `password`, `rol`, `active`) 
                                VALUES ('$loginAdmin', '$nameAdmin', '$surname1Admin', $surname2Admin, '$mailAdmin', NULL, '$passAdmin', 'admin', 1)";

                // Ejecutar todas las consultas
                foreach ($consultas as $consulta) {
                    $conexion->query($consulta);
                }

                $mensajes[] = "Tablas creadas y usuario administrador <strong>$loginAdmin</strong> registrado correctamente.";
                $mensajes[] = "<strong>Instalación de CineLog finalizada con éxito.</strong> (Recuerda eliminar o proteger la carpeta <code>install</code> por seguridad).";
            }
        } catch (Exception $e) {
            $error = true;
            $mensajes[] = "Error durante la instalación: " . $e->getMessage();
        } finally {
            $conexion->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación de CineLog</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Instalación de CineLog</h1>

    <?php if (!empty($mensajes)): ?>
        <div class="mensajes-instalacion" style="padding: 15px; margin-bottom: 20px; border: 1px solid #ccc; background: #f9f9f9;">
            <?php foreach ($mensajes as $msg): ?>
                <p><?php echo $msg; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="install.php" method="post">
        <?php if (file_exists($configFile)): ?>
            <p style="color:red;"><strong>Aviso:</strong> El archivo de configuración ya existe. Si continúa, se usarán esos datos de conexión y se intentarán crear las tablas/administrador.</p>
        <?php else: ?>
            <h2>Base de datos</h2>
            <label for="host">Servidor de base de datos</label>
            <input type="text" name="host" id="host" value="localhost" required>

            <label for="user">Usuario de la base de datos</label>
            <input type="text" name="user" id="user" value="root" required>

            <label for="passDB">Contraseña de la base de datos</label>
            <input type="password" name="passDB" id="passDB">

            <label for="nameDB">Nombre de la base de datos</label>
            <input type="text" name="nameDB" id="nameDB" value="cinelog" required>

            <label for="portDB">Puerto de la base de datos</label>
            <input type="number" name="portDB" id="portDB" value="3306" required>
        <?php endif; ?>

        <h2>Administrador de CineLog</h2>
        <label for="login">Login del administrador</label>
        <input type="text" name="login" id="login" required>

        <label for="name">Nombre del administrador</label>
        <input type="text" name="name" id="name" required>

        <label for="surname1">Primer apellido del administrador</label>
        <input type="text" name="surname1" id="surname1" required>

        <label for="surname2">Segundo apellido (opcional)</label>
        <input type="text" name="surname2" id="surname2">

        <label for="mail">Correo electrónico del administrador</label>
        <input type="email" name="mail" id="mail" required>

        <label for="pass">Contraseña del administrador</label>
        <input type="password" name="pass" id="pass" required>

        <input type="submit" name="install" value="Instalar CineLog">
    </form>
</body>
</html>