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

class AlumnoController extends Controller{

    /**
     * @Route("/alumno", name="alumno_page")
     */
    public function alumnoAction(Request $request){

        // not sure about this
        $defaultData = null;

        // crea el formulario
        $form = $this->createFormBuilder($defaultData)
            ->add('palabraClave',TextType::class, array('constraints'=>array(new Length(array('min'=>3)))))
            ->add('save', SubmitType::class, array('label'=>'Buscar'))
            ->getForm();

        // asignar cosas del request
        $form->handleRequest($request);

        // en caso de que ya haya sido submitteado
        if($form->isSubmitted() && $form->isValid()){
            // poner las cosas del formulario en data
            $data = $form->getData();

            // buscar todos los libros
            $em = $this->getDoctrine()->getManager();
            $result = $em->getRepository("AppBundle\Entity\Libro")->createQueryBuilder('o')
                ->where('o.titulo like :algo and o.existe=1')
                ->groupBy('o.isbn')
                ->setParameter('algo', '%'.$data['palabraClave'].'%')
                ->getQuery()
                ->getResult();

            return $this->render('alumno/catalogo.html.twig', array(
                    'form' => $form->createView(),
                    'mensaje' => 'Numero de resultados: '.count($result),
                    'libros' => $result
                ));
        }

        return $this->render('alumno/catalogo.html.twig', array(
                'form' => $form->createView()
            ));
    }

    /**
     * @Route("/alumno/rentasActivas", name="rentas_activas")
     */
    public function rentasActivasAction(Request $request){

        // manager
        $em = $this->getDoctrine()->getManager();

        $user= $this->get('security.token_storage')->getToken()->getUser();  
        $idAlumno = $user->getIdAlumno();


        $alumno = $em
            ->getRepository('AppBundle:Alumno')
            ->find($idAlumno);

        // ver si ese libro esta en una renta activa
        $repository = $this->getDoctrine()->getRepository('AppBundle:Renta');
        $rentas = $repository->findBy(
            array('idAlumno' => $alumno->getId(), 'activa' => 1)
        );
        
        if($rentas){

            $libros = array();

            foreach ($rentas as $renta) {
                $idmLibro = $renta->getIdLibro();
                $libro = $em
                    ->getRepository('AppBundle:Libro')
                    ->find($idmLibro);
                    //echo $libro->getTitulo();
                    //var_dump($libro);
                array_push($libros, $libro);
            }
            return $this->render('alumno/rentasActivas.html.twig', array(
                "libros" =>$libros
                ));
            //var_dump($rentas);die;
        }else{
            return $this->render('alumno/rentasActivas.html.twig', array(

            ));
        }
        
    }
}
