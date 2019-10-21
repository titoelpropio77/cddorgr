<div class="color-line"></div>
<div class="login-container">
    <div class="row">
        <div class="col-md-12">
            <div class="text-center m-b-md">
                <h3>Iniciar Sesi&oacute;n </h3>
                <small>Secci&oacute;n privada solo personal autorizado con usuario y contrase&ntilde;a</small>
            </div> 
            <div class="hpanel">
                <div class="panel-body" style> 
                    <center><img class="logoImg" src="<?php echo _base_url?>recursos/img/logo.png?v=<?php echo time();?>" width="180" height="56"></center>
                 
                    <?php
					if (isset($_SESSION['mensajes'])) {
						$mensaje = $_SESSION['mensajes'];
						$_SESSION['mensajes'] = '';
						echo $mensaje;
					}
					?>
					
                    <form id="formLogin" name="formLogin" action="<?php echo _base_url . $_GET['uri1']; ?>" method="POST"> 
                        <div class="form-group">
                            <label class="control-label" for="usu_id">Usuario</label>
                            <input type="text" placeholder="Su usuario" required="" value="" name="usu_id" id="usu_id" class="form-control">
                        </div> 
                        <div class="form-group">
                            <label class="control-label" for="usu_password">Contrase&ntilde;a</label>
                            <input type="password" placeholder="Su Contraseña" required="" value="" name="usu_password" id="usu_password" class="form-control">
                            <span class="help-block small"><a href="">¿Me olvide mi contrase&ntilde;a?</a></span>
                        </div>
                        <button class="btn btn-success btn-block" type="submit"><i class="fa fa-user"></i>&nbsp; Ingresar</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <strong>Todo los derechos reservados</strong> <br/> <?php echo _empresa_nombre; ?>
            <br/> <a href="http://orangegroup.com.bo/" target="_blank" style="color:#E67E22;"><?php echo _copy; ?></a>
        </div>
    </div>
</div>