<?php
include 'validar.php';


?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Guarita</title>

    <!-- Core CSS - Include with every page -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../font-awesome/css/font-awesome.css" rel="stylesheet">

    <!-- Page-Level Plugin CSS - Dashboard -->
    <link href="../css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">
    <link href="../css/plugins/timeline/timeline.css" rel="stylesheet">

    <!-- SB Admin CSS - Include with every page -->
    <link href="../css/sb-admin.css" rel="stylesheet">
    <script src="../js/jquery-1.10.2.js"></script>
    
    
     
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>

    <!-- Page-Level Plugin Scripts - Dashboard -->
    <script src="../js/plugins/morris/raphael-2.1.0.min.js"></script>
    <script src="../js/plugins/morris/morris.js"></script>

    <!-- SB Admin Scripts - Include with every page -->
    <script src="../js/sb-admin.js"></script>

    <!-- Page-Level Demo Scripts - Dashboard - Use for reference -->
<!--    <script src="../js/demo/dashboard-demo.js"></script>-->
    
    
             <script src="js/jqBootstrapValidation.js"></script>
            
             <script src="js/bootstrap-inputmask.js"></script>
             <script src="js/bootstrap-inputmask.min.js"></script>
    
              <script  src="js/jquery.pstrength-min.1.2.js"></script>
    
    
          <style type="text/css">
.scroll-area {
	height: 400px;
	position: relative;
	overflow: auto;
}
</style>


<script type="text/javascript">
    
    
    $(document).ready(function() {
                $('.password').pstrength();
            });
            
            
            
             $(document).ready(function() {
                $("#cep").blur(function(e) {
                    if ($.trim($("#cep").val()) != "") {
                        $.getScript("http://cep.republicavirtual.com.br/web_cep.php?formato=javascript&cep=" + $("#cep").val(), function() {
                            if (resultadoCEP["resultado"]) {
                                $("#rua").val(unescape(resultadoCEP["tipo_logradouro"]) + ": " + unescape(resultadoCEP["logradouro"]));
                                $("#bairro").val(unescape(resultadoCEP["bairro"]));
                                $("#cidade").val(unescape(resultadoCEP["cidade"]));
                                $("#estado").val(unescape(resultadoCEP["uf"]));
                            } else {
                                alert("Não foi possivel encontrar o endereço");
                            }
                        });
                    }
                });
            });
            
            
            
            
            
            
            
    
                function mostra(){


                                var div = document.getElementById('divn');
                                var div2 = document.getElementById('divca');
                                div.style.display = 'block';
                                div2.style.display = 'none';
                        };
                        
                        
                         function mostra2(){


                                var div = document.getElementById('divn');
                                var div2 = document.getElementById('divca');
                                div.style.display = 'none';
                                div2.style.display = 'block';
                        };
                        
                        
    
    
    
    
    
    $(document).ready(function(){
           $('.dropdown-toggle').dropdown(); 
        });
        
        
        function verificaEmpr(emp){


                                var div = document.getElementById('div'+ emp.id);
                               

                                if (emp.checked == true)   
                                     
                                        div.style.display = 'block';
                                else
                                        div.style.display = 'none';
                        };
        
        
        
        
       
        
</script>


<style>
    .loader{
        background-image:url(img/ajax-loader.gif);
        background-repeat:no-repeat;
        height:100px;
    }
</style>

</head>

<body>

    <div id="wrapper">

        <?php
include  'configuracao.php';
echo $menu;
?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-6">
                    <h1 class="page-header">Perfil</h1>
                    
                     
                </div>
                
               
                
                
                 <?php
                        
                 
                 
                                $msg = $_GET['msg'];




                                if ($msg != null && $msg <= 5 && ctype_digit($msg)) {


                     switch ($msg) {

                         case 1:

                             echo "<div class='ver'>";


                             echo "<div class='col-lg-6'>";
                             echo "<br>";
                             echo " <div class='alert alert-success alert-dismissable'>
                                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                Registro Salvo Com Sucesso
                            </div>";


                             echo "</div>";

                             echo "</div>";
                             break;
               
               
                                        case 2: 
                                            
                                            
                                            
                                             echo "<div class='ver'>";


                             echo "<div class='col-lg-6'>";
                             echo "<br>";
                             echo " <div class='alert alert-danger alert-dismissable'>
                                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                Algo Deu errado
                            </div>";


                             echo "</div>";

                             echo "</div>";
                             break;
                     }
                 }
                 ?>
                    
               
                   
                     
               
            </div>
            
            
            
            <div class="row">
                <div class="col-lg-12">
                    
                    <div class="panel panel-default">
                        
                        <div class="panel-heading">
                            Meu Perfil
                        </div>
                        
                        
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            
                            
                       
                            
                            
                            
              <form  action="atualizaUsuario.php" method="post" >
                
                <fieldset>
               

                    <legend>Dados pessoais</legend>
                    
                    

                     <div class="col-lg-6" > 
                         
                         
                         
                         
                         <div class="form-group col-lg-8">
                                            <label>Nome Completo</label>
                                            <input class="form-control"
                                                   type="text" 
                                                    required
                                                    name="txtnome"
                                                  
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                   
                   
                    <div class="form-group col-lg-8">
                                            <label>CPF</label>
                                            <input class="form-control"
                                                   type="text" 
                                                   data-mask="999999999-99"
                                                   
                                                    name="txtdocumento"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                   
                   
                     
                     
                   
                   

               
                    </div>
                    
                    
                     <div class="col-lg-6" >  
                         
                   
                        
                         
                         <div class="form-group col-lg-8">
                                            <label>Email</label>
                                            <input class="form-control" 
                                                   type="email" 
                                                    required
                                                    name="txtemail"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                  
                        
                          <div class="form-group col-lg-8">
                                            <label>Telefone</label>
                                            <input class="form-control" data-mask="(99)-99999-9999"
                                                   type="tel" 
                                                    required
                                                    name="txttel"
                                                   
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                         
                         
                    
                    
                         
                        
                       
               
                    </div>
                </fieldset>
                
                
                 <fieldset>
                   <legend>Dados da Conta</legend>

                    <div class="col-lg-6" > 
                  
                       
                        
                        
                        <div class="form-group col-lg-8">
                                            <label>Login</label>
                                            <input class="form-control" 
                                                    
                                                    required
                                                    name="txtlogin"
                                                    id="txtlogin"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                    
                  
                    
                        
                         <div class="form-group col-lg-8">
                                            <label>Senha</label>
                                            <input class=" senha password form-control" 
                                                   type="password"
                                                    required
                                                    name="txtsenha"
                                                   
                                                    id="senha" 
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                        
                        
                   
                        
                      
                        
                        
                        <div class="form-group col-lg-8">
                                            <label>Confirmar Senha</label>
                                            <input class="form-control" 
                                                    
                                                    required
                                                    name="txtconfsenha"
                                                   
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                   
                        </div>
                   
                   
                    <div class="col-lg-6" > 
                  
                    
                    
                        
                        
                        
                        
                         <div class="form-group col-lg-8">
                                            <label>Pergunta</label>
                                            <input class="form-control" 
                                                    
                                                    required
                                                    name="txtpergunta"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                      
                        
                        
                         <div class="form-group col-lg-8">
                                            <label>Resposta</label>
                                            <input class="form-control" 
                                                   
                                                    required
                                                    name="txtresposta"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>

                        
                        </div>
                    
               </fieldset>

              
                <input type="submit" class="btn btn-primary">
                
           </form>
                
                
                
         
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-6 -->
                
            </div>
            
           
        </div>
        <!-- /#page-wrapper -->

        
        
         
                           
        
                            
                           
    </div>
        
        
       
        
    </div>
    <!-- /#wrapper -->

    <!-- Core Scripts - Include with every page -->
    
    
    
        
        
        
        
    

</body>

</html>
