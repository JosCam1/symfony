<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Tarea;
use App\Form\RegistrationFormType; 
use App\Form\LoginFormType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class Controller extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $loginForm = $this->createForm(LoginFormType::class);

        return $this->render('user/index.html.twig', [
            'loginForm' => $loginForm->createView(),
        ]);
    }

    #[Route('/formulario_registro', name:'registro')]
    public function crearUsuario(Request $request, EntityManagerInterface $entityManager): Response
    {
        $error = null;
        $usuario = new Usuario();

        $form = $this->createForm(RegistrationFormType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $usuario->getEmail()]);
            if ($existingUser) {
                $error = 'El usuario ya existe';
            } else {
                $entityManager->persist($usuario);
                $entityManager->flush();

                return $this->redirectToRoute('index');
            }
        }

        $loginForm = $this->createForm(LoginFormType::class);

        return $this->render('registration/register.html.twig', [
            'loginForm' => $loginForm->createView(),
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route('/iniciar_sesion', name: 'inicio_sesion')]
    public function iniciarSesion(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $loginForm = $this->createForm(LoginFormType::class);
        $loginForm->handleRequest($request);

        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $data = $loginForm->getData();

            $user = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);

            if ($user && $user->getPassword() === $data['password']) {
                $session->set('user_id', $user->getId());
                return new RedirectResponse($this->generateUrl('ver_tareas'));
            }

            $error = 'Credenciales inválidas';
            return $this->render('tareas.html.twig', [
                'loginForm' => $loginForm->createView(),
                'error' => $error,
            ]);
        }

        return $this->render('user/index.html.twig', [
            'loginForm' => $loginForm->createView(),
        ]);
    }
    #[Route('/logout', name: 'logout')]
public function logout(SessionInterface $session): Response
{
    // Eliminar todos los datos de la sesión
    $session->invalidate();

    // Redirigir al inicio de sesión
    return $this->redirectToRoute('inicio_sesion');
}

    #[Route("/verTareas", name: 'verTareas')]
    public function verTareas(EntityManagerInterface $entityManager, Request $request, SessionInterface $session): Response
    {
        $idUsuario = $session->get('user_id');
        if (!$idUsuario) {
            return $this->redirectToRoute('inicio_sesion');
        }

        $usuario = $entityManager->getRepository(Usuario::class)->find($idUsuario);

        // Obtener las tareas del usuario
        $tareasUsuario = $entityManager->getRepository(Tarea::class)->findBy(['idUsuario' => $idUsuario]);

        return $this->render('user/tareas.html.twig', [
            'tareas' => $tareasUsuario,
            'usuario' => $usuario,
        ]);
    }

    
    #[Route("/agregar_tarea", name: 'agregar_tarea', methods: ['POST'])]
public function agregarTarea(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): JsonResponse
{
    $jsonData = json_decode($request->getContent(), true);
    $titulo = $jsonData['titulo'];
    $estado = "Pendiente";
    $userId = $session->get('user_id');
    $tarea = new Tarea();

    // Asignar los valores a la nueva tarea
    $tarea->setTitulo($titulo);
    $tarea->setEstado($estado);
    $tarea->setIdUsuario($userId);

    // Persistir la nueva tarea en la base de datos
    $entityManager->persist($tarea);
    $entityManager->flush();

    // Devolver una respuesta JSON exitosa
    return new JsonResponse(['success' => true]);
}

    #[Route("/eliminar_tarea/{id}", name: 'eliminar_tarea')]
public function eliminarTarea($id, EntityManagerInterface $entityManager): Response
{
    $tarea = $entityManager->getRepository(Tarea::class)->find($id);

    if (!$tarea) {
        throw $this->createNotFoundException('No se encontró la tarea.');
    }

    $entityManager->remove($tarea);
    $entityManager->flush();

    return new RedirectResponse($this->generateUrl('ver_tareas'));
}
}