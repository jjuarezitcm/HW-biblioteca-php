<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Validator\Constraints\Length;

use AppBundle\Entity\Renta;

class PersonalController extends Controller{

    /**********************************************************************/
    /**
     * @Route("/personal", name="personal_page")
     */
    public function personalAction(Request $request){
        return $this->render('personal/bienvenida.html.twig');
    }


    /**********************************************************************/
    /**
     * @Route("/personal/prueba", name="prueba_page")
     */
    public function pruebaAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT p
                FROM AppBundle:Renta p
                WHERE p.idAlumno = :id'
            )->setParameter(
                'id', 1
            );
        $renta = $query->getOneOrNullResult();

        $libro = $renta->getIdLibro();

        $libro->setDisponible(0);

        $em->flush();
        
        return new Response("<html><body>el titulo del libro es::::.... ".$libro->getTitulo()."</body></html>");
    }


    /**********************************************************************/
    /**
     * @Route("/personal/prestamos", name="personal_prestamos_page")
     */
    public function prestamosAction(Request $request){

        // ******* crear un formulario
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('idAlumno', TextType::class)
            ->add('idLibro',TextType::class)
            ->add('prestar', SubmitType::class, array('label' => 'Prestar'))
            ->getForm();


        // ******* poner en el form las variables del request
        $form->handleRequest($request);


        // ******* en caso de que se haya hecho submit al formulario
        if($form->isValid()){
            $data = $form->getData();

            // ******* obtener ids
            $idLibro = $data["idLibro"];
            $idAlumno = $data["idAlumno"];


            // ******* validaciones
            if($this->existe('AppBundle:Alumno',$idAlumno)){
                if($this->existe('AppBundle:Libro',$idLibro)){
                    if($this->getAdeudosAlumno($idAlumno) < 2 ){
                        if($this->librosDisponibles($idLibro) > 1){
                            
                            // let's do this
                            $libro = $this->getDoctrine()->getRepository('AppBundle:Libro')->find($idLibro);
                            $alumno = $this->getDoctrine()->getRepository('AppBundle:Alumno')->find($idAlumno);

                            if($libro->getExiste() == 1){
                                $renta = new Renta();
                                $renta->setFecha(new \DateTime());
                                $renta->setActiva(1);
                                $renta->setIdLibro($libro);
                                $renta->setIdAlumno($alumno);

                                $libro->setDisponible(0);

                                $nombreAlumno = $alumno->getNombre();
                                $nombreLibro = $libro->getTitulo();
                                $idLibro = $libro->getId();

                                $em = $this->getDoctrine()->getManager();

                                // tells Doctrine you want to (eventually) save the Product (no queries yet)
                                $em->persist($renta);
                                $em->persist($libro);

                                // actually executes the queries (i.e. the INSERT query)
                                $em->flush();

                                // ******* crear un formulario
                                $defaultData = array();
                                $form = $this->createFormBuilder($defaultData)
                                    ->add('idAlumno', TextType::class)
                                    ->add('idLibro',TextType::class)
                                    ->add('prestar', SubmitType::class, array('label' => 'Prestar'))
                                    ->getForm();

                                return $this->render('personal/prestamos.html.twig', array(
                                   'form' => $form->createView(),
                                   'mensaje' => 'Al alumno: "'.$nombreAlumno.'"" se le entrego el libro: "'.$nombreLibro.'"" con id: "'.$idLibro.'"'
                                ));
                            }else{
                                // no hay suficientes libros
                                   return $this->render('personal/prestamos.html.twig', array(
                                   'form' => $form->createView(),
                                   'mensaje' => 'El libro no existe'
                                ));
                            }

                        }else{
                            // no hay suficientes libros
                               return $this->render('personal/prestamos.html.twig', array(
                               'form' => $form->createView(),
                               'mensaje' => 'no hay suficientes libros'
                            ));
                        }
                    }else{
                        // aqui el alumno tiene ya dos libros rentados
                           return $this->render('personal/prestamos.html.twig', array(
                           'form' => $form->createView(),
                           'mensaje' => 'el alumno excede adeudos'
                        ));
                    }
                }else{
                    // aqui no existe el libro
                       return $this->render('personal/prestamos.html.twig', array(
                       'form' => $form->createView(),
                       'mensaje' => 'el libro no existe'
                    ));
                }
            }else{
                // aqui no existe el alumno
                   return $this->render('personal/prestamos.html.twig', array(
                   'form' => $form->createView(),
                   'mensaje' => 'el alumno no existe'
                ));
            }

        }

        // ******* se muestra un formulario vacio
        return $this->render('personal/prestamos.html.twig', array(
            'form' => $form->createView()));
    }


    /**********************************************************************/
    private function existe($entidad, $id){
        $em = $this->getDoctrine()->getManager();
        $registro = $em->find($entidad.'', $id);
        if($registro){
            return true;
        }else{
            return false;
        }
    }


    /**********************************************************************/
    private function getAdeudosAlumno($idAlumno){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Renta');
        $rentas = $repository->findBy(
            array('idAlumno' => $idAlumno, 'activa' => 1)
        );
        return count($rentas);
    }


    /**********************************************************************/
    private function librosDisponibles($idLibro){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT p
                FROM AppBundle:Libro p
                WHERE p.id = :id'
            )->setParameter(
                'id', $idLibro
            );
        $libro = $query->getOneOrNullResult();
        //$libro = $libro[0];
        $isbn = $libro->getIsbn();

        $repository = $this->getDoctrine()->getRepository('AppBundle:Libro');
        $libros = $repository->findBy(
            array('isbn'=>$isbn, 'disponible'=>1)
        );

        return count($libros);
    }


    /**********************************************************************/
    /**
     * @Route("/personal/recepcion", name="personal_recepcion_page")
     */
    public function recepcionAction(Request $request){
        
        // crear un formulario
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('idLibro',TextType::class)
            ->add('devolver', SubmitType::class, array('label' => 'Devolver'))
            ->getForm();
        
        // poner en el form las variables del request
        $form->handleRequest($request);

        if($form->isValid()){
            // post stuff
            $data = $form->getData();

            $idLibro = $data['idLibro'];

            $mensaje = '';

            if($this->existe('AppBundle:Libro',$idLibro)){

                // buscar el registro de la renta correspondiente
                $em = $this->getDoctrine()->getManager();
                $renta = $em->getRepository("AppBundle\Entity\Renta")->createQueryBuilder('o')
                    ->where('o.idLibro = :algo and o.activa = 1')
                    ->setParameter('algo', $idLibro)
                    ->getQuery()
                    ->getOneOrNullResult();

                if($renta){
                    $renta->setActiva(0);
                    $libro = $renta->getIdLibro();
                    $libro->setDisponible(1);

                    $em->persist($renta);
                    $em->persist($libro);

                    $em->flush();

                    $mensaje = 'Transacción exitosa, recibiste: "'.$libro->getTitulo().'" con id: "'.$libro->getId().'"';
                }else{
                    $mensaje = 'El libro no está prestado';
                }

            }else{
                $mensaje = 'El libro no existe';
            }

            // crear un formulario
            $defaultData = array();
            $form = $this->createFormBuilder($defaultData)
                ->add('idLibro',TextType::class)
                ->add('devolver', SubmitType::class, array('label' => 'Devolver'))
                ->getForm();

            // render
            return $this->render('personal/recepcion.html.twig', array(
                'form' => $form->createView(),
                'mensaje' => $mensaje
                ));
        }

        return $this->render('personal/recepcion.html.twig', array(
            'form' => $form->createView()));
    }
}
