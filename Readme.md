<strong>Slim Framework</strong> es una herramienta ideal para construir proyectos no excesivamente grandes de manera rápida, ordenada y aprovechando según tus necesidades las nuevas tendencias en programación que se están usando hoy en día, por lo que usando <strong>Slim</strong> vas a poder configurar tu proyecto para usar componentes de otros framework a tu gusto, pudiendo usar namespaces, autocargado de librerías y demás funcionalidades que hoy en día están muy de moda.

Para la gestión de las dependencias vamos a usar <strong>composer</strong>.Instalaremos <strong>Slim Framework</strong> y <strong>Doctrine 2</strong> para para trabajar con BD. Además de instalar lo dicho, vamos a preparar un path en nuestro proyecto para el autocargado de nuestras propias clases siguiendo el <strong>estandar PSR-4</strong>. 
Pra empezar como es lógico debemos de disponer de composer instalado en el sistema. Comenzamos:

Creamos nuestro directorio para el proyecto:
```
mkdir proyecto
chmod 777 proyecto
cd proyecto
```

Una vez dentro del directorio de nuestro proyecto creamos nuestro fichero <strong>composer.json</strong> con el siguiente contenido, que nos descargará la última versión de <strong>Slim</strong>, el <strong>ORM Doctrine 2</strong> y nos prepara un path que hace referencia a la carpeta src/ de nuestro proyecto cuyo namespace será Custom siguiendo el estándar psr-4 que es el que recomienda composer en estos momentos.
```
{
    "require": {
        "slim/slim": "^2.6",
        "doctrine/orm": "*"
    },
    "autoload": {
        "psr-4": {"Custom\\": "src/"}
    }
}
```

Ejecutamos en la consola con el comando "<strong>composer install</strong>" y si no ha habido problemas y todo se ha instalado correctamente, nuestras dependencias estarán disponibles ya en el directorio vendor de nuestro proyecto. Además crearemos la carpeta src en el raíz para nuestras propias clases. 

Lo siguiente es configurar <strong>Doctrine</strong> para que podamos usar la consola que nos facilita el uso de un montón de tareas a la hora  de trabajar con BD como autogeneración de entidades, validación de las mismas, etc... Más info sobre el uso de la consola aquí: <a href="http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/tools.html" target="_blank">Documentación consola Doctrine</a>.

Para ponerse manos a la obra en nuestro proyecto vamos a crear un fichero llamado bootstrap.php que usaremos de cargador de librerías y configuración. En el usaremos el cargador de composer, instanciaremos Slim y pondremos algo de configuración necesaria:

```
<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

//Doctrine
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = array(
    __DIR__ . "/src"
);

$isDevMode = false;
// the connection configuration
$dbParams = array(
    'driver' => 'pdo_mysql',
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => '',
    'dbname' => 'doctrine',
);

$configDoctrine = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);

$entityManager = EntityManager::create($dbParams, $configDoctrine);

```

Para habilitar la consola debemos crear un fichero en el raíz de nuestro proyecto llamado "<strong>cli-config.php</strong>" donde usaremos las librerías Doctrine para la consola y el bootstrap de configuración del proyecto en el cual hemos definido los parametros de conexión a nuesta base de datos además de alguna cosa más y hemos creado una instancia del <strong>entityManager</strong> para el trabajo con <strong>Doctrine</strong>.

```
<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once 'bootstrap.php';

return ConsoleRunner::createHelperSet($entityManager);

```

Ahora ya nos podemos ir a la consola del sistema y en el directorio del proyecto usar la <strong>consola Doctrine</strong> para ejecutar tareas:
```
php vendor/bin/doctrine
```
Si al ejecutar el siguiente comando obtenemos una respuesta como esta es que la consola ya está disponible:
```
Doctrine Command Line Interface version 2.5.1

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help                            Displays help for a command
  list                            Lists commands
 dbal
  dbal:import                     Import SQL file(s) directly to Database.
  dbal:run-sql                    Executes arbitrary SQL directly from the 
  ....
```

Vamos a crear una entidad en nuestro path que va a representar una tabla de ejemplo para trabajar en nuestro proyecto en la carpeta src/entity/Test.php con el siguiente código:
```
<?php

//Una vez creada la entidad ejecutamos la consola de Doctrine para que cree esta tabla en la base de datos
//php vendor/bin/doctrine orm:schema-tool:create

namespace Custom\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Test
 *
 * @ORM\Table(name="test")
 * @ORM\Entity
 */
class Test {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @ORM\Column(name="age", type="integer", length=3)
     */
    private $age;


    public function __get($property) {

        if (property_exists($this, $property)) {
            return $this->$property;
        }

    }

    public function __set($property, $value) {

        if (property_exists($this, $property)) {

            $this->$property = $value;

        }
    }
}

```

Ejecutamos la consola de Doctrine y nos creará automáticamente la tabla en la BD configurada en bootstrap:
```
php vendor/bin/doctrine orm:schema-tool:create
```

Podemos insertar unos cuanto registros en nuestra tabla para pruebas:
```
INSERT INTO `test` (`id`, `name`, `age`) VALUES
    (1, 'John Snow', 33),
    (2, 'Albert Jackson', 45),
    (3, 'Sonia Smith', 39),
    (4, 'Mary Johnson', 49),
    (5, 'Tom Hardy', 25);
```

Ahora creamos un <strong>index.php</strong> en nuestro raíz y creamos una ruta con <strong>Slim</strong> donde usaremos el <strong>entityManager</strong> para cargar los registros y renderizaremos un template para la vista de nuestra ruta:
```
<?php

require 'bootstrap.php';

//Ruta para generar un csv desde una tabla de base de datos
$app->get('/home(/)', function () use ($app,$entityManager) {

    $result = $entityManager->getRepository('\Custom\Entity\Test')->findAll();

    $app->render('home.phtml', array(
        'result' => $result
    ));

});

$app->run();
```

Creamos la carpeta <strong>templates</strong> en el raíz y la vista dentro de esta con el siguiente contenido:
```
<!DOCTYPE html>
<html>
<head>
    <title>Slim Framework + Doctrine + Custom Path en Composer</title>
</head>
<body>
<h1>Usando Doctrine 2 en Slim Framework</h1>

<?php

echo '<table>';

foreach ($result as $row) {

    echo '<tr>
            <td>'.$row->__get('name').'</td>
            <td>'.$row->__get('age').'</td>
          </tr>';
}

echo '</table>';

?>

</body>
</html>

```

Y listo, tenemos un proyecto <strong>Slim con Doctrine 2</strong> y su consola disponible además de un path para cargar nuestras clases personalizadas. Fácil, rápido y ligero.
<a href="https://github.com/malakana/slim-doctrine-composer" target="_blank" >Aquí podéis descargar el proyecto de prueba del tutorial desde mi cuenta de git</a>.
