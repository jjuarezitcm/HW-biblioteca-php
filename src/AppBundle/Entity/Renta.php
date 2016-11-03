<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Renta
 *
 * @ORM\Table(name="Renta", indexes={@ORM\Index(name="fk_alumno", columns={"id_alumno"}), @ORM\Index(name="fk_libro", columns={"id_libro"})})
 * @ORM\Entity
 */
class Renta
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activa", type="boolean", nullable=false)
     */
    private $activa;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Libro
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Libro")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_libro", referencedColumnName="id")
     * })
     */
    private $idLibro;

    /**
     * @var \AppBundle\Entity\Alumno
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Alumno")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_alumno", referencedColumnName="id")
     * })
     */
    private $idAlumno;



    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return Renta
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set activa
     *
     * @param boolean $activa
     *
     * @return Renta
     */
    public function setActiva($activa)
    {
        $this->activa = $activa;

        return $this;
    }

    /**
     * Get activa
     *
     * @return boolean
     */
    public function getActiva()
    {
        return $this->activa;
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
     * Set idLibro
     *
     * @param \AppBundle\Entity\Libro $idLibro
     *
     * @return Renta
     */
    public function setIdLibro(\AppBundle\Entity\Libro $idLibro = null)
    {
        $this->idLibro = $idLibro;

        return $this;
    }

    /**
     * Get idLibro
     *
     * @return \AppBundle\Entity\Libro
     */
    public function getIdLibro()
    {
        return $this->idLibro;
    }

    /**
     * Set idAlumno
     *
     * @param \AppBundle\Entity\Alumno $idAlumno
     *
     * @return Renta
     */
    public function setIdAlumno(\AppBundle\Entity\Alumno $idAlumno = null)
    {
        $this->idAlumno = $idAlumno;

        return $this;
    }

    /**
     * Get idAlumno
     *
     * @return \AppBundle\Entity\Alumno
     */
    public function getIdAlumno()
    {
        return $this->idAlumno;
    }
}
