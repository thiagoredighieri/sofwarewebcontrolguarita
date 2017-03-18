<?php
include 'validar.php';
include '../conecta.php';

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
    
    
    
    $('.tooltip-demo').tooltip({
        selector: "[data-toggle=tooltip]",
        container: "body"
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
        
        $(document).on('click', '.car-proc', function(e){
            e.preventDefault();
            
            $(".modal-body").html('');
            $(".modal-body").addClass('loader');
            $("#myModal").modal('show');
            
            $.post('cartao.php',
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
                            Tabela Responsaveis
                        </div>
                        
                        
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <!--<table class="table table-striped table-bordered table-hover">-->
                                
                                <div class="">
                                    <p>
                                        <a type="button" href="NovoVeiculo.php?msg=" class=" btn btn-outline btn-primary" data-toggle="modal" >Novo</a>

                                    </p>

                                </div>
                               
                                
                                 <div class=" col-md-offset-7 col-md-5" >

                                    
                                    <form action="listaRpesquisa.php?pagina=1&itens=A&ordem=A&msg=" method="post">
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
                                                                    <tr >
                                                                        <th style="text-align: center;">Id</th>
                                                                        <th style="text-align: center;">Nome</th>
                                                                        
                                                                        <th style="text-align: center;">Estado</th>
                                                                        <th style="text-align: center;">Nº Cartão</th>
                                                                        <th style="text-align: center;">Ações</th>
                                                                    </tr>

											<?php
											
											
											$res = mysqli_query($con,"select registrado.pessoa_idPessoa, pessoa.nomePessoa, registrado.estadoCadastro, registrado.num_cadastro
																				from pessoa, registrado 
																				where pessoa.idPessoa=registrado.pessoa_idPessoa"); 
												
												
												while($escrever=mysqli_fetch_array($res)){

												echo '<tr>';

													echo '<td>'.$escrever['pessoa_idPessoa'].'</td>';

													echo '<td>'.$escrever['nomePessoa'].'</td>';
													
													echo '<td>'.$escrever['estadoCadastro'].'</td>';
													
													echo '<td>'.$escrever['num_cadastro'].'</td>';
													
													
													echo '<td><a href="#" class="edit-proc btn btn-primary" data-id=":numCadastro" title="Editar"><i class="fa fa-edit"></i> </a></td>';
													
													

													echo '</tr>';

												
												}
											
											echo "</table>"
						
											
										?>
								
								
								
                                    
                                
                                <div class="btn-group dropup">
                                        <button class="btn btn-primary" type="button">10</button>

                                        <div class="btn-group">
                                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Itens Página <span class="caret"></span></button>
                                            <ul class="dropdown-menu" >
                                                <li><a href="listaR.php?pagina=1&itens=A&ordem=A&msg=">10</a></li>
                                                <li><a href="listaR.php?pagina=1&itens=B&ordem=A&msg=">20</a></li>
                                                <li><a href="listaR.php?pagina=1&itens=C&ordem=A&msg=">50</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                
                                
                               
                                
                                <div class="btn-group dropup">
                                        <button class="btn btn-primary" type="button">10</button>

                                        <div class="btn-group">
                                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Ordenar por <span class="caret"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="listaR.php?pagina=1&itens=A&ordem=A&msg=">Crescente</a></li>
                                                <li><a href="listaR.php?pagina=1&itens=A&ordem=B&msg=">Descrecente</a></li>
                                                
                                                
                                                
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
                                           
                                            <h4 class="modal-title" id="myModalLabel"></h4>
                                        </div>
                                        <div class="modal-body">
                                           
                                        </div>
                                        <div class="modal-footer">
                                            <?php echo "<a type='button' class='btn btn-default'  href='?pagina=$pc&itens=$itens&ordem=$or&msg='>Sair</a>";?>
                                            
                                        </div>
                                    </div>
                                     
                                </div>
                                 
         </div>
        
        
        
    

</body>

</html>
