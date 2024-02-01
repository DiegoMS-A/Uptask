<?php

namespace Controllers;

use MVC\Router;
use Model\Proyecto;
use Model\Usuario;

class DashboardController
{
    public static function index(Router $router)
    {
        session_start();
        isAuth();

        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos'=> $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router)
    {
        session_start();        
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD']==='POST'){
            $proyecto = new Proyecto($_POST);

            //Validación nombre proyecto 
            $alertas = $proyecto->validarProyecto();
            
            if(empty($alertas)){

                //Generar URL
                $hash = md5(uniqid());
                $proyecto->url = $hash;

                //Almacenar creador proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                //GUARDAR PROYECTO
                $proyecto->guardar();
                header('Location: /proyecto?id=' . $proyecto->url);

            }
        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router)
    {
        session_start();        
        isAuth();
        $alertas = [];

        $token = $_GET['id'];

        if(!$token) header('Location: /dashboard');
     
        //Revisar que la persona que entra en proyecto es el creador
        $proyecto = Proyecto::where('url', $token);

        if($proyecto->propietarioId !== $_SESSION['id']){
            header('Location: /dashboard');
        } 

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto,
            'alertas' => $alertas
        ]);
    }

    public static function perfil(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario->sincronizar($_POST);

            $alertas = $usuario->validar_perfil();

            if(empty($alertas)) {

                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario && $existeUsuario->id !== $usuario->id ) {
                    // Mensaje de error
                    Usuario::setAlerta('error', 'Email no válido, ya pertenece a otra cuenta');
                    $alertas = $usuario->getAlertas();
                } else {
                    // Guardar el registro
                    $usuario->guardar();

                    Usuario::setAlerta('exito', 'Guardado Correctamente');
                    $alertas = $usuario->getAlertas();

                    // Asignar el nombre nuevo a la barra
                    $_SESSION['nombre'] = $usuario->nombre;
                }
            }
        }
        
        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_password(Router $router){
        session_start();
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);

            //Sincronizar con los datos del usuario

            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevo_password();

            if(empty($alertas)) {
                $resultado = $usuario->comprobarPassword();

                if($resultado){

                    $usuario->password = $usuario->password_nuevo;
                    
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);

                    $usuario->hashearPassword();

                   $resultado = $usuario->guardar();

                   if($resultado){
                    Usuario::setAlerta('exito', 'Password cambiado correctamente');
                    $alertas = Usuario::getAlertas();
                   }
                    
                }else{
                    Usuario::setAlerta('error', 'Password incorrecto');
                    $alertas = Usuario::getAlertas();
                }
            }
        }

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
}