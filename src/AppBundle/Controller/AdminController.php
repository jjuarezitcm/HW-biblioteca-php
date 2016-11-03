<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Validator\Constraints\Length;

use AppBundle\Form\LibroType;
use AppBundle\Entity\Libro;
use AppBundle\Entity\User;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use AppBundle\Form\UserType;

use AppBundle\Entity\Alumno;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\HttpFoundation\Session\Session;



class AdminController extends Controller{
    /**
     * @Route("/admin", name="admin_page")
     */
    public function adminAction(Request $request){

        /*
        $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
        $txt = "John Doe\n";
        fwrite($myfile, $txt);
        $txt = "Jane Doe\n";
        fwrite($myfile, $txt);
        fclose($myfile);
        */
        

        return $this->render('admin/bienvenida.html.twig');
    }

    /**
     * @Route("/admin/ejemplaresDisponibles", name="ejemplares_disponibles")
     */
    public function ejemplaresDisponiblesAction(Request $request){

        $this->fileEjemplaresDisponibles();

        $file = new File("ReporteLibrosDisponibles.txt");
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
        //return $this->render('admin/bienvenida.html.twig');
    }

    /**
     * @Route("/admin/ejemplaresEnPrestamo", name="ejemplares_en_prestamo")
     */
    public function ejemplaresEnPrestamoAction(Request $request){

        $this->fileReporteRentasActivas();

        $file = new File("ReporteRentas.txt");
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
        //return $this->render('admin/bienvenida.html.twig');
    }

    /**
     * @Route("/admin/altaLibro", name="alta_libro")
     */
    public function altaLibroAction(Request $request){

        // 1) build the form
        $libro = new Libro();
        $form = $this->createForm(LibroType::class, $libro);

        // 2) handle the submit
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            
            $libro->setDisponible(1);
            $libro->setExiste(1);

            // 3) save the book
            $em = $this->getDoctrine()->getManager();
            $em->persist($libro);
            $em->flush();

            $titulo = $libro->getTitulo();
            $nuevoId = $libro->getId();

            unset($libro);
            unset($form);
            $libro = new Libro();
            $form = $this->createForm(LibroType::class,$libro);

            return $this->render('admin/altaLibro.html.twig',
                array('form' => $form->createView(), "mensaje" => "Se registró: '".$titulo."'' con el id: ".$nuevoId));
        }

        return $this->render('admin/altaLibro.html.twig',
                array('form' => $form->createView()));
    }


    /**
     * @Route("/admin/altaAlumno", name="alta_alumno")
     */
    public function altaAlumnoAction(Request $request){ 

        // construir el formulario
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('matricula',TextType::class)
            ->add('nombre',TextType::class)
            ->add('apellidoPaterno',TextType::class)
            ->add('apellidoMaterno',TextType::class)
            ->add('direccion',TextType::class)
            ->add('telefono',TextType::class)
            ->add('email',TextType::class)
            ->add('carrera',TextType::class)
            ->add('username',TextType::class)
            ->add('password', RepeatedType::class, array(
                        'type' => PasswordType::class,
                        'invalid_message' => 'The password fields must match.',
                        'options' => array('attr' => array('class' => 'password-field')),
                        'required' => true,
                        'first_options'  => array('label' => 'Password'),
                        'second_options' => array('label' => 'Repeat Password'),
                ))
            ->add('registrar', SubmitType::class, array('label' => 'Registrar'))
            ->getForm();


        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $user = new User();
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPlainPassword($data['password']);

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $user->addRole('ROLE_USER');
            $user->addRole('ROLE_ALUMNO');

            $alumno = new Alumno();
            $alumno->setMatricula($data['matricula']);
            $alumno->setNombre($data['nombre']);
            $alumno->setApellidoPaterno($data['apellidoPaterno']);
            $alumno->setApellidoMaterno($data['apellidoMaterno']);
            $alumno->setDireccion($data['direccion']);
            $alumno->setTelefono($data['telefono']);
            $alumno->setEmail($data['email']);
            $alumno->setCarrera($data['carrera']);

            $matriculaAlumno = $alumno->getMatricula();
            

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($alumno);
            $em->flush();

            $user->setIdAlumno($alumno->getId());
            $user->setEnabled(1);

            $em->persist($user);
            $em->flush();

            $idAlumno = $alumno->getId();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            $defaultData = array();
            $form = $this->createFormBuilder($defaultData)
                ->add('matricula',TextType::class)
                ->add('nombre',TextType::class)
                ->add('apellidoPaterno',TextType::class)
                ->add('apellidoMaterno',TextType::class)
                ->add('direccion',TextType::class)
                ->add('telefono',TextType::class)
                ->add('email',TextType::class)
                ->add('carrera',TextType::class)
                ->add('username',TextType::class)
                ->add('password', RepeatedType::class, array(
                            'type' => PasswordType::class,
                            'invalid_message' => 'The password fields must match.',
                            'options' => array('attr' => array('class' => 'password-field')),
                            'required' => true,
                            'first_options'  => array('label' => 'Password'),
                            'second_options' => array('label' => 'Repeat Password'),
                    ))
                ->add('registrar', SubmitType::class, array('label' => 'Registrar'))
                ->getForm();

            return $this->render('admin/altaAlumno.html.twig',
                array('form' => $form->createView(),
                      'mensaje' => 'Se insertó el alumno: '.$matriculaAlumno.' con el id: '.$idAlumno));
        }

        return $this->render('admin/altaAlumno.html.twig',
                array('form' => $form->createView()));
    }


    /**
     * @Route("/admin/altaPersonal", name="alta_personal")
     */
    public function altaPersonalAction(Request $request){ 

        // construir el formulario
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('email',TextType::class)
            ->add('username',TextType::class)
            ->add('password', RepeatedType::class, array(
                        'type' => PasswordType::class,
                        'invalid_message' => 'The password fields must match.',
                        'options' => array('attr' => array('class' => 'password-field')),
                        'required' => true,
                        'first_options'  => array('label' => 'Password'),
                        'second_options' => array('label' => 'Repeat Password'),
                ))
            ->add('registrar', SubmitType::class, array('label' => 'Registrar'))
            ->getForm();


        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $user = new User();
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPlainPassword($data['password']);

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $user->addRole('ROLE_USER');
            $user->addRole('ROLE_PERSONAL');


            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $user->setIdAlumno(0);
            $user->setEnabled(1);

            $em->persist($user);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            // construir el formulario
            $defaultData = array();
            $form = $this->createFormBuilder($defaultData)
                ->add('email',TextType::class)
                ->add('username',TextType::class)
                ->add('password', RepeatedType::class, array(
                            'type' => PasswordType::class,
                            'invalid_message' => 'The password fields must match.',
                            'options' => array('attr' => array('class' => 'password-field')),
                            'required' => true,
                            'first_options'  => array('label' => 'Password'),
                            'second_options' => array('label' => 'Repeat Password'),
                    ))
                ->add('registrar', SubmitType::class, array('label' => 'Registrar'))
                ->getForm();

            return $this->render('admin/altaPersonal.html.twig',
                array('form' => $form->createView(),
                      'mensaje' => 'Transaccion exitosa, se registro el usuario: '.$user->getUserName()));
        }

        return $this->render('admin/altaPersonal.html.twig',
                array('form' => $form->createView()));
    }


    /**
     * @Route("/admin/consultaLibros", name="consulta_libros")
     */
    public function consultaLibrosAction(Request $request, $resultado=null){
        // ******* crear un formulario
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('terminoABuscar', TextType::class)
            ->add('prestar', SubmitType::class, array('label' => 'Buscar'))
            ->getForm();

        // handle submit
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $libros = $this->buscaLibros($data["terminoABuscar"]);

            //
            return $this->render('admin/consultaLibros.html.twig', array(
                                "form" => $form->createView(),
                                "mensaje" => "el termino: ".$data['terminoABuscar']." resultados: ".count($libros),
                                "libros" => $libros
                                ));            
        }
        $mensajeDeTransaccion = $request->getSession()->get('notice');
        if($mensajeDeTransaccion){
            $resultado = $mensajeDeTransaccion;
            $request->getSession()->remove('notice');
        }
        return $this->render('admin/consultaLibros.html.twig', array("form"=>$form->createView(), "mensaje" =>$resultado));
    }

    /**
     * @Route("/admin/consultaPersonal", name="consulta_personal")
     */
    public function consultaPersonalAction(Request $request){
                // ******* crear un formulario
                $defaultData = array();
                $form = $this->createFormBuilder($defaultData)
                    ->add('emailABuscar', TextType::class)
                    ->add('buscar', SubmitType::class, array('label' => 'Buscar'))
                    ->getForm();

                // handle submit
                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()){
                    $data = $form->getData();

                    $repository = $this->getDoctrine()->getRepository('AppBundle:User');
                    $alumno = $repository->findOneByEmail($data['emailABuscar']);

                    if($alumno){
                        return $this->render('admin/consultaPersonal.html.twig', array(
                                            "form" => $form->createView(),
                                            "mensaje" => "Resultados: ".count($alumno),
                                            "alumnos" => $alumno
                                            )); 
                    }else{
                        return $this->render('admin/consultaPersonal.html.twig', array(
                                            "form" => $form->createView(),
                                            "mensaje" => "No hay resultados"
                                            )); 
                    }
                    //
                               
                }

                return $this->render('admin/consultaPersonal.html.twig', array("form"=>$form->createView()));
    }

    /**
     * @Route("/admin/consultaAlumno", name="consulta_alumno")
     */
    public function consultaAlumnoAction(Request $request, $resultado=null){
        // ******* crear un formulario
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('matriculaABuscar', TextType::class)
            ->add('buscar', SubmitType::class, array('label' => 'Buscar'))
            ->getForm();

        // handle submit
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $repository = $this->getDoctrine()->getRepository('AppBundle:Alumno');
            $alumno = $repository->findOneByMatricula($data['matriculaABuscar']);
//var_dump($alumno);die;
            //

            if($alumno){
                return $this->render('admin/consultaAlumno.html.twig', array(
                                    "form" => $form->createView(),
                                    "mensaje" => "Resultados: ".count($alumno),
                                    "alumno" => $alumno
                                    ));  
            }else{
                return $this->render('admin/consultaAlumno.html.twig', array(
                                    "form" => $form->createView(),
                                    "mensaje" => "Resultados: ".count($alumno),
                                    ));  
            }          
        }

        $mensajeDeTransaccion = $request->getSession()->get('notice');
        if($mensajeDeTransaccion){
            $resultado = $mensajeDeTransaccion;
            $request->getSession()->remove('notice');
        }

        return $this->render('admin/consultaAlumno.html.twig', array("form"=>$form->createView(), "mensaje"=>$resultado));
    }


    /**
     * @Route("/admin/modificarLibro/{id}", name="modificar_libro")
     */
    public function modificarLibroAction(Request $request, $id){

        $em = $this->getDoctrine()->getManager();

        $libro = $em
            ->getRepository('AppBundle:Libro')
            ->find($id);

        $form = $this->createFormBuilder($libro)
            ->add('id', TextType::class, array("disabled" => true))
            ->add('isbn', TextType::class)
            ->add('titulo', TextType::class)
            ->add('autor', TextType::class)
            ->add('editorial', TextType::class)
            ->add('anio', TextType::class)
            ->add('paginas', TextType::class)
            ->add('ubicacion', TextType::class)
            ->add('disponible', CheckboxType::class, array('required'=>false))
            ->add('modificar', SubmitType::class, array('label' => 'Modificar'))
            ->getForm();

        // handle request
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $data = $form->getData();

            $libro = $this->getDoctrine()
                ->getRepository('AppBundle:Libro')
                ->find($libro->getId());

            $libro->setIsbn($libro->getIsbn());
            $libro->setTitulo($libro->getTitulo());
            $libro->setAutor($libro->getAutor());
            $libro->setDisponible($libro->getDisponible());

            $em->flush();

            return $this->render('admin/modificaLibro.html.twig', array("form"=>$form->createView(), "mensaje"=>"Transaccion exitosa"));
        }

        return $this->render('admin/modificaLibro.html.twig', array("form"=>$form->createView()));
    }

    /**
     * @Route("/admin/eliminarLibro/{id}", name="eliminar_libro")
     */
    public function eliminarLibroAction(Request $request, $id){
        // mensaje a devolver
        $resultado = '';

        // ver si ese libro esta en una renta activa
        $repository = $this->getDoctrine()->getRepository('AppBundle:Renta');
        $rentas = $repository->findBy(
            array('idLibro' => $id, 'activa' => 1)
        );

        if(count($rentas)==0){
            // manejador
            $em = $this->getDoctrine()->getManager();

            // buscar libro
            $libro = $em
                ->getRepository('AppBundle:Libro')
                ->find($id);

            $libro->setExiste(0);

            // "eliminar libro"
            $em->flush();

            $resultado = "Transaccion exitosa";
        }else{
            $resultado = "Por cuestiones de integridad referencial no deberías borrar ese libro... por favor contacta al administrador";
        }

        $request->getSession()->set('notice', $resultado);

        /*
        $response = $this->forward('AppBundle:Admin:consultaLibros', array(
                'resultado'  => $resultado
            ));
        */
        return $this->redirectToRoute('consulta_libros');
        //return $response;
    }

    /**
     * @Route("/admin/eliminarAlumno/{id}", name="eliminar_alumno")
     */
    public function eliminarAlumnoAction(Request $request, $id){
        // mensaje a devolver
        $resultado = '';

        // ver si ese alumno esta en una renta activa
        $repository = $this->getDoctrine()->getRepository('AppBundle:Renta');
        $rentas = $repository->findBy(
            array('idAlumno' => $id, 'activa' => 1)
        );

        if(count($rentas)==0){

            // manejador
            $em = $this->getDoctrine()->getManager();

            //eliminar historial de rentas
            $qb = $em->createQueryBuilder();
            $query = $qb->delete('AppBundle:Renta', 'rent')
                        ->where('rent.idAlumno = :alumnoId')
                        ->setParameter('alumnoId', $id)
                        ->getQuery();

            $query->execute();

            // buscar libro
            $alumno = $em
                ->getRepository('AppBundle:Alumno')
                ->find($id);

            $repository = $this->getDoctrine()->getRepository('AppBundle:User');
            $user = $repository->findOneBy(
                array('idAlumno' => $alumno->getId())
            );

            // "eliminar libro"
            $em->remove($alumno); 

            $em->remove($user);
            $em->flush();

            $resultado = "Transaccion exitosa";
        }else{
            $resultado = "Por cuestiones de integridad referencial no deberías borrar ese alumno... por favor contacta al administrador";
        }

        $request->getSession()->set('notice', $resultado);

        /*
        $response = $this->forward('AppBundle:Admin:consultaAlumno', array(
                'resultado'  => $resultado
            ));

        return $response;
        */
        return $this->redirectToRoute('consulta_alumno');
    }

    /**
     * @Route("/admin/eliminarPersonal/{id}", name="eliminar_personal")
     */
    public function eliminarPersonalAction(Request $request, $id){
        // mensaje a devolver
        $resultado = '';

        // manejador
        $em = $this->getDoctrine()->getManager();

        // buscar libro
        $personal = $em
            ->getRepository('AppBundle:User')
            ->find($id);

        $em->remove($personal);
        $em->flush();

        $resultado = "Transaccion exitosa";

        /*
        $response = $this->forward('AppBundle:Admin:consultaPersonal', array(
                'resultado'  => $resultado
            ));

        return $response;
        */
        return $this->redirectToRoute('consulta_personal');
    }

    /**
     * @Route("/admin/modificarAlumno/{id}", name="modificar_alumno")
     */
    public function modificarAlumnoAction(Request $request, $id){

        $em = $this->getDoctrine()->getManager();

        $alumno = $em
            ->getRepository('AppBundle:Alumno')
            ->find($id);

        $form = $this->createFormBuilder($alumno)
            ->add('matricula', TextType::class, array("disabled" => true))
            ->add('nombre',TextType::class)
            ->add('apellidoPaterno',TextType::class)
            ->add('apellidoMaterno',TextType::class)
            ->add('direccion',TextType::class)
            ->add('telefono',TextType::class)
            ->add('email', TextType::class)
            ->add('carrera', TextType::class)
            ->add('modificar', SubmitType::class, array('label' => 'Modificar'))
            ->getForm();

        // handle request
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $data = $form->getData();

            $alumno = $this->getDoctrine()
                ->getRepository('AppBundle:Alumno')
                ->find($alumno->getId());

            $alumno->setNombre($alumno->getNombre());
            $alumno->setApellidoPaterno($alumno->getApellidoPaterno());
            $alumno->setApellidoMaterno($alumno->getApellidoMaterno());
            $alumno->setDireccion($alumno->getDireccion());
            $alumno->setTelefono($alumno->getTelefono());

            $alumno->setEmail($alumno->getEmail());
            $alumno->setCarrera($alumno->getCarrera());

            $em->flush();

            return $this->render('admin/modificarAlumno.html.twig', array("form"=>$form->createView(), "mensaje"=>"Transaccion exitosa"));
        }

        return $this->render('admin/modificarAlumno.html.twig', array("form"=>$form->createView()));
    }

    /**
     * @Route("/admin/modificarPersonal/{id}", name="modificar_personal")
     */
    public function modificarPersonalAction(Request $request, $id){

        $em = $this->getDoctrine()->getManager();

        $alumno = $em
            ->getRepository('AppBundle:User')
            ->find($id);

        $form = $this->createFormBuilder($alumno)
            ->add('email', TextType::class)
            ->add('username', TextType::class)
            ->add('modificar', SubmitType::class, array('label' => 'Modificar'))
            ->getForm();

        // handle request
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $data = $form->getData();

            $alumno = $this->getDoctrine()
                ->getRepository('AppBundle:User')
                ->find($alumno->getId());

            $alumno->setUsername($alumno->getUsername());
            $alumno->setEmail($alumno->getEmail());

            $em->flush();

            return $this->render('admin/modificarPersonal.html.twig', array("form"=>$form->createView(), "mensaje"=>"Transaccion exitosa"));
        }

        return $this->render('admin/modificarPersonal.html.twig', array("form"=>$form->createView()));
    }

    /*************************************************************/
    private function buscaLibros($keyWord){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT p
                FROM AppBundle:Libro p
                WHERE (p.id = :id
                OR p.isbn = :id
                OR p.titulo LIKE :key
                OR p.autor LIKE :key)
                AND p.existe = true'
            )->setParameter(
                'id', $keyWord
            )->setParameter(
                'key', "%".$keyWord."%"
            );

        $libros = $query->getResult();

        return $libros;
    }

    /*************************************************************/
    private function fileEjemplaresDisponibles(){
        $em = $this->getDoctrine()->getManager();
        $records = $em->getRepository('AppBundle:Libro')->findAll();

        // obtener todos los isbn
        $listaISBN = array();
        for($i = 0; $i < count($records); $i++){
            array_push($listaISBN, $records[$i]->getIsbn());
            //$listaISBN[$i] = $records[$i]->getIsbn();
        }

        // eliminar repeticiones de isbn
        $listaISBN = array_unique($listaISBN);

        $archivo = fopen("ReporteLibrosDisponibles.txt", "w+") or die("no se pudo abrir el archivo");
        fwrite($archivo,"Ejemplar,Cantidad\n");

        // ver cuantos ejemplares disponibles por isbn
        foreach($listaISBN as $isbn){
            $query = $em->createQuery(
                'SELECT p
                FROM AppBundle:Libro p
                WHERE p.isbn = :isbn AND p.existe = 1
                AND p.disponible = true'
                )->setParameter('isbn' , $isbn);
            $libros = $query->getResult();

            if($libros){
            $nombre = $libros[0]->getTitulo();
            $disponibles = count($libros);

            $txt = $nombre.",".$disponibles."\n";
            fwrite($archivo,$txt);
            }
        }
        fclose($archivo);
    }

    /*************************************************************/
    private function fileReporteRentasActivas(){
        $em = $this->getDoctrine()->getManager();
        $records = $em->getRepository('AppBundle:Renta')->findAll();

        $archivo = fopen("ReporteRentas.txt","w+") or die("no se pudo abrir");

        $txt = "FECHA HORA,ID LIBRO,TITULO LIBRO,ID ALUMNO,NOMBRE ALUMNO\n";
        fwrite($archivo, $txt);

        foreach($records as $record){
            if($record->getactiva()){
                // libro y alumno
                $libro = $record->getIdLibro();
                $alumno = $record->getIdAlumno();

                $fecha = $record->getFecha()->format('Y-m-d H:i:s');
                $idLibro = $libro->getId();
                $tituloLibro = $libro->getTitulo();
                $idAlumno = $alumno->getId();
                $nombreAlumno = $alumno->getNombre();

                $txt = $fecha.",".$idLibro.",".$tituloLibro.",".$idAlumno.",".$nombreAlumno;
                fwrite($archivo, $txt);
            }
        }
        fclose($archivo);
    }
}
