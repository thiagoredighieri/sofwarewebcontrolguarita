

<!DOCTYPE html>
<html lang="en">
 <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <!-- Core CSS - Include with every page -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">

    <!-- SB Admin CSS - Include with every page -->
    <link href="css/sb-admin.css" rel="stylesheet">
    
    <style>
        
        .profile-img
{
    width: 96px;
    height: 96px;
    margin: 0 auto 10px;
    display: block;
    -moz-border-radius: 50%;
    -webkit-border-radius: 50%;
    border-radius: 50%;
}
    </style>

</head>

<body>

    <div class="container">
        <div class="row">
            
            
			<?php

            if(isset($_GET["erro"])){
				
               $erro = $_GET["erro"];
			    echo "<CENTER><FONT color='red'>$erro</font></center>";
            }

			?>
            
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Acesse sua conta</h3>
                    </div>
                    <div class="panel-body">
                        
                        <form class="form-signin" action="controlador.php" method="post">
                       
							<input type="text" name="operacao" value= "validarUsuario" style="display: none"/>
                         <img class="profile-img" src="img/photo.png"
                                                             accesskey="" alt="">

                          <div class="form-group">
                                    <input class="form-control"  name="login"  required autofocus placeholder="Login">
                                </div>
                         
                         
                          <div class="form-group">
                              <input class="form-control" required placeholder="Senha" name="senha" type="password" value="">
                                </div>
                         
                         <button class="btn btn-lg btn-primary btn-block" type="submit">Entrar</button>
                        
                        <a href="Senha/lembrar_senha.php?errn=" class="btn btn-link" >Esqueci minha senha</a>
                        
                      </form>
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Scripts - Include with every page -->
    <script src="js/jquery-1.10.2.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>

    <!-- SB Admin Scripts - Include with every page -->
    <script src="js/sb-admin.js"></script>

</body>
</html>
