<div class='contenedor olvide'>

    <?php include_once __DIR__ . '\..\templates\nombre-sitio.php';  ?>

    <div class="contenedor-sm">
        <p class="descripcion-pagina">Recupera tu password</p>
        <?php include_once __DIR__ . '\..\templates\alertas.php';  ?>

        <form class="formulario" method="POST" action="/olvide" novalidate>
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Tu email" name="email" />
            </div>
            <input type="submit" class="boton" value="Envíar" />
        </form>
        <div class="acciones">
            <a href="/">Logearse</a>
            <a href="/crear">Crea una cuenta</a>
        </div>
    </div><!--.contenedor-sm-->

</div>