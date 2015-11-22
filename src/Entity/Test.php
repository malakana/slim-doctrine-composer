<?php

//Una vez creada la entidad ejecutamos la consola de doctrine para que cree esta tabla en la base de datos
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
