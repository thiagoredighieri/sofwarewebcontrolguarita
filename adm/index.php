<?php
include 'validar.php';
include  'configuracao.php';
echo $header;
echo $menu;
?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Painel Administrativo</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-8">
                    
                    <!-- /.panel -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bar-chart-o fa-fw"></i> Passagens Diária
                            <div class="pull-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-xs dropdown-toggle"  data-toggle="dropdown">
                                        Ações
                                        <i class="fa fa-share-square-o"></i>
                                    </button>
                                    <ul class="dropdown-menu pull-right" role="menu">
                                        <li><a href="admin.php">Atualizar Dados</a>
                                        </li>
                                        <li><a href="listaPassagemGeral.php?pagina=1&itens=A&ordem=A&msg=">Ver Tabela</a>
                                        </li>
                                        
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    
                                    <div>
                                    <p>
                                        <strong>Cadastrados</strong>
                                        <span class="pull-right text-muted"></span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="28" aria-valuemin="0" aria-valuemax="100" style="width:">
                                            <span class="sr-only">10% Complete (success)</span>
                                        </div>
                                    </div>
                                </div>
                                    
                                    <div>
                                    <p>
                                        <strong>Visitantes</strong>
                                        <span class="pull-right text-muted"></span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100" style="width:">
                                            <span class="sr-only">90% Complete (success)</span>
                                        </div>
                                    </div>
                                </div>
                                   
                                </div>
                                <!-- /.col-lg-4 (nested) -->
                                
                                <!-- /.col-lg-8 (nested) -->
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                  
                </div>
                <!-- /.col-lg-8 -->
                
                <!-- /.col-lg-4 -->
                
                
               
                
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- Core Scripts - Include with every page -->
    

</body>

</html>

