<?php
include 'validar.php';


?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin</title>

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
        
        $(document).on('click', '.voltar-home', function(e){
            e.preventDefault();

            $("#list").addClass('loader');
            $("#list").load('list.php?idpessoa='+$(this).attr('data-id'), function(){
                $("#list").removeClass('loader');
            });

        });
        
        $(document).on('click', '.edit-record', function(e){
            e.preventDefault();
            
            $(".modal-body").html('');
            $(".modal-body").addClass('loader');
            $("#dialog-example").modal('show');
            
            $.post('edit.php',
                     {id: $(this).attr('data-id')},
                     function(html) {
                        $(".modal-body").removeClass('loader');
                        $(".modal-body").html(html);
            });
        });
        
        $(document).on('click', '.add_proced', function(e){
            e.preventDefault();
            
            $(".modal-body").html('');
            $(".modal-body").addClass('loader');
            $("#dialog-example").modal('show');
            
            $.post('list_proc.php',
                     {id: $(this).attr('data-id')},
                     function(html) {
                        $(".modal-body").removeClass('loader');
                        $(".modal-body").html(html);
            });
        });
        
        $(document).on('click', '.criar-nota', function(e){
            e.preventDefault();
            
            $(".modal-body").html('');
            $(".modal-body").addClass('loader');
            $("#myModal").modal('show');
            
            $.post('VisualizarPedido.php',
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
                    <h1 class="page-header">Passagem</h1>
                    
                     
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
                           Tabela Passagem Geral
                        </div>
                        
                        
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <!--<table class="table table-striped table-bordered table-hover">-->
                                
                                <form target="_blank" action="../PDF/pdfGeral.php" method="post">
                                      
                               
                                    
                                    <div class="form-group input-group" style="display: none">
                                       <input type="text" class="form-control" placeholder="Nome" name="txtdia" value="<?php echo "$data";?>">
                                            
                                        </div>
                                    
                                    
                                    <button  type="submit"  class=" btn btn-danger" ><i class="fa fa-save"></i> Salvar</button>

                                                
                                           
                                    
                                    </form>
                                
                                
                                
                                
                              
                                 <div class=" col-md-offset-7 col-md-5" >

                                    
                                    <form action="listaPassagemGeral_P.php?pagina=1&itens=A&ordem=A&msg=" method="post">
                                   <div class="form-group input-group">
                                       <input type="text" class="form-control" placeholder="Nome ou Nº Cartão" name="nome">
                                            <span class="input-group-btn">
                                                <button class="btn btn-default" type="submit" ><i class="fa fa-search"></i>
                                                </button>
                                            </span>
                                        </div>
                                    
                                   
                                     <br> <br> 
                                   </form>
                                
                                </div>
                                
                                
                                
                            
                                     <table class="table table-bordered table-striped" >
                                                                    <tr  >
                                                                        <th>Responsável</th>
                                                                        <th>DataEntrada</th>
                                                                        <th>DataSaida</th>
                                                                        <th>Finalidade</th>
                                                                        <th>Modelo</th>
                                                                        <th>Marca</th>
                                                                        <th>Cor</th>
                                                                        <th>Placa</th>
                                                                        <th>Nº Cadastro</th>
                                                                        <th>Usuario</th>
                                                                        <th>Tipo</th>
                                                                    </tr>

                                                                   
                                                                </table>

              
                                   
                                    
                                  
                                
                               
                                            
                                
                                <div class="btn-group dropup">
                                        <button class="btn btn-primary" type="button"></button>

                                        <div class="btn-group">
                                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Itens Página <span class="caret"></span></button>
                                            <ul class="dropdown-menu" >
                                                <li><a href="listaPassagemGeral.php?pagina=1&itens=A&ordem=A&msg=">10</a></li>
                                                <li><a href="listaPassagemGeral.php?pagina=1&itens=B&ordem=A&msg=">20</a></li>
                                                <li><a href="listaPassagemGeral.php?pagina=1&itens=C&ordem=A&msg=">50</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                
                                
                               
                                
                                <div class="btn-group dropup">
                                        <button class="btn btn-primary" type="button"></button>

                                        <div class="btn-group">
                                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Ordenar por <span class="caret"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="listaPassagemGeral.php?pagina=1&itens=A&ordem=A&msg=">Crescente</a></li>
                                                <li><a href="listaPassagemGeral.php?pagina=1&itens=A&ordem=B&msg=">Descrecente</a></li>
                                                
                                                
                                                
                                            </ul>
                                        </div>
                                    </div>
                                
                                
                                
                                
                        </div>
                        <!-- /.panel-body -->
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
