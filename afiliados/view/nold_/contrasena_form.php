<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            <div class="panel-heading">
                <h3>Cambiar Contrase&ntilde;a</h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo _base_url . $_GET['uri1']; ?>" method="POST" class="form-horizontal">

                    <div class="hr-line-dashed"></div>
                    <div class="form-group"><label class="col-lg-2 control-label">Usuario</label>
                        <div class="col-lg-10"><input type="text" disabled="" placeholder="<?php echo $_SESSION['afil_id']; ?>" class="form-control"></div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group"><label class="col-sm-2 control-label">Contrase&ntilde;a Antigua</label>
                        <div class="col-sm-10"><input type="password" class="form-control" name="usu_password_antiguo"></div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group"><label class="col-sm-2 control-label">Contrase&ntilde;a Nueva</label>
                        <div class="col-sm-10"><input type="password" class="form-control" name="usu_password"></div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-2">
                            <button class="btn btn-default" type="reset">Cancelar</button>
                            <button class="btn btn-primary" type="submit">Cambiar Contrase&ntilde;a</button>
                        </div>
                    </div>
                </form> 
            </div>
        </div>
    </div>
</div>
