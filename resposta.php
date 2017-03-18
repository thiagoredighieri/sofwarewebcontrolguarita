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
                        
                 
              $msg = $_GET['msg'];       
            
            if ($msg != null && $msg <= 5 && ctype_digit($msg)) {


                     switch ($msg) {

                         case 1:

                             echo "<div class='ver'>";


                             echo "<div class='col-md-4 col-md-offset-4'>";
                             echo "<br>";
                             echo " <div class='alert alert-success alert-dismissable'>
                                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                Você efetuou sua solicitação com sucesso! Entraremos em contato com você dentro de 7 dias para confirmar sua solicitação.
                            <a type='submit' class='btn btn-primary' href='index.php' >
        
            Página Inicial
          </a>
</div>";


                             echo "</div>";

                             echo "</div>";
                             break;
               
               
                                        case 2: 
                                            
                                            
                                            
                                    echo "<div class='ver'>";


                             echo "<div class='col-md-4 col-md-offset-4'>";
                             echo "<br>";
                             echo " <div class='alert alert-danger alert-dismissable'>
                                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                Algo Due errado !
                            </div>";


                             echo "</div>";

                             echo "</div>";
                             break;
                     }
                 }
                 ?>
            
         
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
