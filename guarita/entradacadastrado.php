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
                        
                        
    
    
    $(function(){

        $("#list").addClass('loader');
        $("#list").load('list.php?idpessoa=<? echo $_GET[idpessoa]; ?>', function(){
            $("#list").removeClass('loader');
        });

        $(document).on('click', '.list-proc', function(e){
            e.preventDefault();

            $("#list").addClass('loader');
            $("#list").load('list_proc.php?idconta='+$(this).attr('data-id'), function(){
                $("#list").removeClass('loader');
            });

        });
        
        
        
        
        $(document).on('click', '.criar-nota', function(e){
            e.preventDefault();
            
            $(".modal-body").html('');
            $(".modal-body").addClass('loader');
            $("#myModal").modal('show');
            
            $.post('VisualizarCadastrado.php',
                     {id: $(this).attr('data-id')},
                     function(html) {
                        $(".modal-body").removeClass('loader');
                        $(".modal-body").html(html);
            });
        });
        
        $(document).on('click', '.criar-proc', function(e){
            e.preventDefault();
            
            $(".modal-body").html('');
            $(".modal-body").addClass('loader');
            $("#myModal").modal('show');
            
            $.post('NovoVeiculo.php',
                     {id: $(this).attr('data-id')},
                     function(html) {
                         
                        $(".modal-body").removeClass('loader');
                        $(".modal-body").html(html);
            });
        });
        
        $(document).on('click', '.edit-proc', function(e){
           
            e.preventDefault();
            
            
           
     
            
            $(".modal-body").html('');
            $(".modal-body").addClass('loader');
            $("#myModal").modal('show');
            
            $.post('EditarPedido.php',
                     {id: $(this).attr('data-id')},
                     function(html) {
                         
                        $(".modal-body").removeClass('loader');
                        $(".modal-body").html(html);
            });
        });
        
        $(document).on('click', '.del-proc', function(e){
            e.preventDefault();
            
            $(".modal-body").html('');
            $(".modal-body").addClass('loader');
            $("#dialog-example").modal('show');
            
            $.post('del_proc.php',
                     {id: $(this).attr('data-id')},
                     function(html) {
                        $(".modal-body").removeClass('loader');
                        $(".modal-body").html(html);
            });
        });
    });
    
    $('.selectpicker').selectpicker({
      style: 'btn-info',
      size: 4
  });
    
    
    $(document).ready(function(){
           $('.dropdown-toggle').dropdown(); 
        });
        
        
        
        
        
       
        
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
                    <h1 class="page-header">Principal</h1>
                    
                     
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
                <div class="col-lg-offset-3 col-lg-6">
                    
                    <div class="panel panel-default">
                        
                        <div class="panel-heading">
                          Entrada de Veiculos Cadastrados
                        </div>
                        
                        
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                        <form class="valida"  action="pesquisaCadastrado.php?msg=" method="post">
                
                <fieldset>
              
                    <div class="col-lg-offset-3 col-lg-7">
								
                                                                
                                                            <div class="controls-row">
                                                                <div class="input-prepend">


                                                                     <div class="row-fluid">
                                                                            <label for="id-date-picker-1">Número do Cartão</label>
                                                                        </div>
                                                                    <div class="form-group input-group">
                                                                        <input type="number" class="form-control" name="txtnumcartao">
                                                                        
                                                                    </div>

                                                                

                                                                  
                                                                      </div>
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
    
    
    
         <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                           
                                            <h4 class="modal-title" id="myModalLabel"></h4>
                                        </div>
                                        <div class="modal-body">
                                           
                                        </div>
                                        <div class="modal-footer">
                                            <?php echo "<a type='button' class='btn btn-default'  href='?msg='>Sair</a>";?>
                                            
                                        </div>
                                    </div>
                                     
                                </div>
                                 
         </div>
        
        
        
    

</body>

</html>
