<div class='contenedor login'>

<?php include_once __DIR__ . '\..\templates\nombre-sitio.php';  ?>

    <div class="contenedor-sm">
        <p class="descripcion-pagina">Iniciar sesión</p>
        <?php include_once __DIR__ . '\..\templates\alertas.php';  ?>
        <form class="formulario" method="POST" action="/" novalidate>
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Tu email" name="email"/>
            </div>
            <div class="campo">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Tu password" name="password" />
            </div>
            <input type="submit" class="boton" value="Envíar" />
        </form>
        <div class="acciones">
            <a href="/crear">Crea una cuenta</a>
            <a href="/olvide">Olvide mi password</a>
        </div>
    </div><!--.contenedor-sm-->

</div>