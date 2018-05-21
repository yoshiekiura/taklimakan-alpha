<?php

namespace App\Entity\udf;

// For future Doctrine use. Now ORM modificators are purposefully left without "@" symbol
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\udf\SymbolRepository")
 */
class Symbol
{
    public function __construct($_name, $_description, $_exchange, $_type) {
        $this->name = $_name;
        $this->description = $_description;
        $this->exchange = $_exchange;
        $this->type = $_type;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * ORM\Column(type="string", length=32)
     */
    private $name;
    public function getName() {
        return $this->name;
    }

    /**
     * ORM\Description()
     * ORM\Column(type="string", length=256)
     */
    private $description;
    public function getDescription() {
        return $this->description;
    }

    /**
     * ORM\Exchange()
     * ORM\Column(type="string", length=32)
     */
    private $exchange;
    public function getExchange() {
        return $this->exchange;
    }

    /**
     * ORM\Type()
     * ORM\Column(type="string", length=32)
     */
    private $type;
    public function getType() {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getName();
    }
}
