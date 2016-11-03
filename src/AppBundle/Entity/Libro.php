<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Libro
 *
 * @ORM\Table(name="Libro")
 * @ORM\Entity
 */
class Libro
{
    /**
     * @var string
     *
     * @ORM\Column(name="isbn", type="string", length=30, nullable=false)
     */
    private $isbn;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="string", length=70, nullable=false)
     */
    private $titulo;
    /**
     * @var string
     *
     * @ORM\Column(name="editorial", type="string", length=70, nullable=false)
     */
    private $editorial;

    /**
     * @var string
     *
     * @ORM\Column(name="anio", type="string", length=70, nullable=false)
     */
    private $anio;

    /**
     * @var string
     *
     * @ORM\Column(name="paginas", type="integer",  nullable=false)
     */
    private $paginas;

    /**
     * @var string
     *
     * @ORM\Column(name="autor", type="string", length=70, nullable=false)
     */
    private $autor;

    /**
     * @var string
     *
     * @ORM\Column(name="ubicacion", type="string", length=70, nullable=false)
     */
    private $ubicacion;

    /**
     * @var boolean
     *
     * @ORM\Column(name="disponible", type="boolean", nullable=false)
     */
    private $disponible;

    /**
     * @var boolean
     *
     * @ORM\Column(name="existe", type="boolean", nullable=false)
     */
    private $existe;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set isbn
     *
     * @param string $isbn
     *
     * @return Libro
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Get isbn
     *
     * @return string
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * Set titulo
     *
     * @param string $titulo
     *
     * @return Libro
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return string
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set autor
     *
     * @param string $autor
     *
     * @return Libro
     */
    public function setAutor($autor)
    {
        $this->autor = $autor;

        return $this;
    }

    /**
     * Get autor
     *
     * @return string
     */
    public function getAutor()
    {
        return $this->autor;
    }

    /**
     * Set disponible
     *
     * @param boolean $disponible
     *
     * @return Libro
     */
    public function setDisponible($disponible)
    {
        $this->disponible = $disponible;

        return $this;
    }

    /**
     * Get disponible
     *
     * @return boolean
     */
    public function getDisponible()
    {
        return $this->disponible;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set existe
     *
     * @param boolean $existe
     *
     * @return Libro
     */
    public function setExiste($existe)
    {
        $this->existe = $existe;

        return $this;
    }

    /**
     * Get existe
     *
     * @return boolean
     */
    public function getExiste()
    {
        return $this->existe;
    }

    /**
     * Set editorial
     *
     * @param string $editorial
     *
     * @return Libro
     */
    public function setEditorial($editorial)
    {
        $this->editorial = $editorial;

        return $this;
    }



    /**
     * Get editorial
     *
     * @return string
     */
    public function getEditorial()
    {
        return $this->editorial;
    }

    /**
     * Set anio
     *
     * @param string $anio
     *
     * @return Libro
     */
    public function setAnio($anio)
    {
        $this->anio = $anio;

        return $this;
    }

    /**
     * Get anio
     *
     * @return string
     */
    public function getAnio()
    {
        return $this->anio;
    }

    /**
     * Set paginas
     *
     * @param integer $paginas
     *
     * @return Libro
     */
    public function setPaginas($paginas)
    {
        $this->paginas = $paginas;

        return $this;
    }

    /**
     * Get paginas
     *
     * @return integer
     */
    public function getPaginas()
    {
        return $this->paginas;
    }

    /**
     * Set ubicacion
     *
     * @param string $ubicacion
     *
     * @return Libro
     */
    public function setUbicacion($ubicacion)
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    /**
     * Get ubicacion
     *
     * @return string
     */
    public function getUbicacion()
    {
        return $this->ubicacion;
    }
}
