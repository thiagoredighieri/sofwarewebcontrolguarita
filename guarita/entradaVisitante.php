<?php
include 'validar.php';


?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin - Localiza</title>

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
    
    
    
          <style type="text/css">
.scroll-area {
	height: 400px;
	position: relative;
	overflow: auto;
}
</style>


<script type="text/javascript">
    
    
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
                        
  
    
    $('.selectpicker').selectpicker({
      style: 'btn-info',
      size: 4
  });
    
    
    $(document).ready(function(){
           $('.dropdown-toggle').dropdown(); 
        });
        
        
        function verificaEmpr(emp){


                                var div = document.getElementById('div'+ emp.id);
                               
                               alert("ddd");

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
                    <h1 class="page-header">Entrada</h1>
                    
                     
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
                            Novo Cadastro
                            
                            
                        </div>
                        
                         <br> <br> 
                        
                         
                         <div   > 
                    
                             <form class="valida"  action="buscaVisitante.php?pagina=1&itens=A&ordem=A&msg=" method="post">
                                   <div class="form-group input-group">
                                       <input type="text" class="form-control" placeholder="Consultar Visitante" name="nome">
                                            <span class="input-group-btn">
                                                <button class="btn btn-default" type="submit" ><i class="fa fa-search"></i>
                                                </button>
                                            </span>
                                        </div>
                                </form>    
                                  
                    </div> 
                        
                        
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            
                             
                            
                        <form class="valida"  action="cadastraPassagem.php" method="post">
                
                <fieldset>
               

                    <legend>Dados pessoais</legend>

                     <div class="col-lg-6" > 
                         
                         
                         
                         <div class="form-group col-lg-9">
                                            <label>Nome Completo</label>
                                            <input class="form-control"
                                                   type="text" 
                                                    required
                                                    name="txtnome"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                   
                    

                         
                   <br><br><br><br>
                    
                    
                    
                   
                   
                    <div class="form-group col-lg-9">
                                            <label>Documento</label>
                                            <input class="form-control"
                                                   type="text" 
                                                    required
                                                    name="txtdocumento"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                   
                   
                  
                   
                             <div class="form-group col-lg-9">
                                            <label>Finalidade</label>
                                            <textarea class="form-control" rows="4" required
                                                    name="txtfinalidade" ></textarea>
                                        </div>
                   
                     
                     
                   
                  

               
                    </div>
                    
                    
                   
                    
                </fieldset>

               <fieldset>
                   <legend>Dados do Veículo</legend>

                    <div class="col-lg-6" > 
                  
                       
                        
                        
                        <div class="form-group col-lg-8">
                                            <label>Marca</label>
                                            <input class="form-control" 
                                                    
                                                    required
                                                    name="txtmarca"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                    
                  
                    
                        
                         <div class="form-group col-lg-8">
                                            <label>Cor</label>
                                            <input class="form-control" 
                                                    
                                                    required
                                                    name="txtcor"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                        
                   
                        
                      
                        
                        
                       <div class="form-group col-lg-6">
                                            <label>Ano</label>
                                            <input class="form-control" 
                                                   
                                                    required
                                                    data-mask="9999"
                                                    name="txtano"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                   
                        </div>
                   
                   
                    <div class="col-lg-6" > 
                  
                    
                    
                        
                        
                        
                        
                        <div class="form-group col-lg-8">
                                            <label>Modelo</label>
                                            <input class="form-control" 
                                                 
                                                    required
                                                    name="txtmodelo"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                      
                        <div class="form-group col-lg-6">
                                            <label>Placa</label>
                                            <input class="form-control" 
                                                    data-mask="aaa-9999"
                                                    required
                                                    name="txtplaca"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                        
                        
                        
                        
                        
                        <div class="form-group col-lg-6">
                                            <label>Local</label>
                                            <input class="form-control" 
                                                   
                                                    required
                                                    
                                                    name="txtlocal"
                                                    
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
