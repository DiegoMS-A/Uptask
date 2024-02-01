<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController
{
    public static function login(Router $router)
    {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();


            if (empty($alertas)) {
                //VERIFICAR QUE EXISTE EL USUARIO
                $usuario = Usuario::where('email', $usuario->email);

                if (!$usuario || $usuario->confirmado === "0") {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                } else {
                    //El usuario existe
                    if (password_verify($_POST['password'], $usuario->password)) {

                        session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        header('Location: /dashboard');

                    } else {
                        Usuario::setAlerta('error', 'Password Incorrecto');
                    }
                }

            }

        }
        $alertas = Usuario::getAlertas();
        //RENDER A LA VISTA
        $router->render('auth/login', [
            'titulo' => 'Iniciar sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout()
    {
       session_start();
       $_SESSION = [];
       header('Location: /');
    
    }

    public static function crear(Router $router)
    {
        $alertas = [];
        $usuario = new Usuario();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if (empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);
                if ($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya está registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    //CREAR NUEVO USUARIO
                    //Hashear password
                    $usuario->hashearPassword();

                    //Eliminar password2
                    unset($usuario->password2);

                    //Generar el token
                    $usuario->crearToken();


                    //Crear nuevo usuario
                    $resultado = $usuario->guardar();

                    //Generar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();
                    //debuguear($email);

                    if ($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }

        }

        $router->render('auth/crear', [
            'titulo' => 'Crea tu cuenta en UpTask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router)
    {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if (empty($alertas)) {

                $usuario = Usuario::where('email', $usuario->email);

                if ($usuario && $usuario->confirmado === "1") {

                    //Generar nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);
                    //Actualizar el usuario
                    $usuario->guardar();
                    //Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();
                    //Imprimir la alerta        
                    Usuario::setAlerta('exito', 'Las instrucciones han sido enviadas a tu email');

                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');

                }

            }

        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi password',
            'alertas' => $alertas
        ]);

    }

    public static function reestablecer(Router $router)
    {
        $token = s($_GET['token']);
        $mostrar = true;

        if (!$token) {
            header('Location: /');
        }

        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
            $mostrar = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Añadir el nuevo password
            $usuario->sincronizar($_POST);
            //Validar password
            $alertas = $usuario->validarPassword();

            if (empty($alertas)) {
                //hashear password
                $usuario->hashearPassword();
                //Eliminar token
                $usuario->token = null;
                //Guardar usuario
                $resultado = $usuario->guardar();
                //Redireccionar
                if ($resultado) {
                    Header('Location: /');
                }
            }
        }


        $alertas = Usuario::getAlertas();

        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer mi password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function confirmar(Router $router)
    {

        $token = s($_GET['token']);

        if (!$token)
            header('Location: /');

        //Encontrar usuario con el token

        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            //No hay usuarios con este token
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            //Confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta',
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router)
    {
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada OK'
        ]);

    }
}
