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
            
            $.post('VisualizarUsuario.php',
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
            
            $.post('EditarUsuario.php',
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
                    <h1 class="page-header">Cadastros</h1>
                    
                     
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
                            Tabela Usuários
                        </div>
                        
                        
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <!--<table class="table table-striped table-bordered table-hover">-->
                                
                                <div class="">
                                    <p>
                                        <a type="button" href="NovoUsuario.php?msg=" class=" btn btn-outline btn-primary" data-toggle="modal" >Novo</a>

                                    </p>

                                </div>
                                
                                <div class=" col-md-offset-7 col-md-5" >

                                    
                                    <form action="listaUpesquisa.php?pagina=1&itens=A&ordem=A&msg=" method="post">
                                   <div class="form-group input-group">
                                       <input type="text" class="form-control" placeholder="Nome" name="nome">
                                            <span class="input-group-btn">
                                                <button class="btn btn-default" type="submit" ><i class="fa fa-search"></i>
                                                </button>
                                            </span>
                                        </div>
                                    
                                   
                                     <br> <br> 
                                   </form>
                                
                                </div>
                                
                            
                                     <table class="table table-bordered table-striped" >
                                                                    <tr >
                                                                        <th>Id</th>
                                                                        <th>Nome Completo</th>
                                                                        <th>Usuário</th>
                                                                        <th>Email</th>
                                                                        <th>Estado</th>
                                                                        <th>Ações</th>
                                                                    </tr>

                                         </table>   
                                
                                <div class="btn-group dropup">
                                        <button class="btn btn-primary" type="button">10</button>

                                        <div class="btn-group">
                                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Itens Página <span class="caret"></span></button>
                                            <ul class="dropdown-menu" >
                                                <li><a href="listaU.php?pagina=1&itens=A&ordem=A&msg=">10</a></li>
                                                <li><a href="listaU.php?pagina=1&itens=B&ordem=A&msg=">20</a></li>
                                                <li><a href="listaU.php?pagina=1&itens=C&ordem=A&msg=">50</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                
                                
                               
                                
                                <div class="btn-group dropup">
                                        <button class="btn btn-primary" type="button">10</button>

                                        <div class="btn-group">
                                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Ordenar por <span class="caret"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="listaU.php?pagina=1&itens=A&ordem=A&msg=">Crescente</a></li>
                                                <li><a href="listaU.php?pagina=1&itens=A&ordem=B&msg=">Descrecente</a></li>
                                                
                                                
                                                
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
    
    
    
         <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            
                                            
                                        </div>
                                        <div class="modal-body">
                                           
                                        </div>
                                        <div class="modal-footer">
                                          
                                            
                                        </div>
                                    </div>
                                     
                                </div>
                                 
         </div>
        
        
        
    

</body>

</html>
