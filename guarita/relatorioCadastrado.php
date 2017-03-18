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

  
    
   <link rel="stylesheet" href="ui/jquery-ui-1.10.4.custom.css">

 
  <script src="js/jquery-ui-1.10.3.custom.js"></script>
    
    
    
          <style type="text/css">
.scroll-area {
	height: 400px;
	position: relative;
	overflow: auto;
}
</style>


<script>
            
             $(function() {
    $( "#datepicker" ).datepicker();
  });
 
             $(document).ready(function() {   

        $('.date-picker').datepicker();
        $('#timepicker1').timepicker({
          minuteStep: 1,
          showSeconds: true,
          showMeridian: false
        });
                
        $('.colorpicker').colorpicker();
        $('.colorpicker input').click(function() {
          $(this).parents('.colorpicker').colorpicker('show');
        })


        $('.knob').knob();

        var $upper = $('#upper');

        $('#images').refineSlide({
            transition : 'random',
            onInit : function () {
                var slider = this.slider,
                   $triggers = $('.translist').find('> li > a');

                $triggers.parent().find('a[href="#_'+ this.slider.settings['transition'] +'"]').addClass('active');

                $triggers.on('click', function (e) {
                   e.preventDefault();

                    if (!$(this).find('.unsupported').length) {
                        $triggers.removeClass('active');
                        $(this).addClass('active');
                        slider.settings['transition'] = $(this).attr('href').replace('#_', '');
                    }
                });

                function support(result, bobble) {
                    var phrase = '';

                    if (!result) {
                        phrase = ' not';
                        $upper.find('div.bobble-'+ bobble).addClass('unsupported');
                        $upper.find('div.bobble-js.bobble-css.unsupported').removeClass('bobble-css unsupported').text('JS');
                    }
                }

                support(this.slider.cssTransforms3d, '3d');
                support(this.slider.cssTransitions, 'css');
            }
        });

      });
            
             
		function _mostraDivi() {
		var divEsc = document.getElementById("dia");
                var divEsc2 = document.getElementById("periodo");
		
		divEsc.style.display='block';
                divEsc2.style.display='none';
		
		
                }
                
                
                function _mostraDivi2() {
		var divEsc = document.getElementById("dia");
                var divEsc2 = document.getElementById("periodo");
		
		divEsc.style.display='none';
                divEsc2.style.display='block';
		
		
                }
            
            function _mostra () {
                
                $('#cartao').attr('disabled', false);
                $('. btn btn-mini').attr('disabled', true);
                $('#salvar').attr('disabled', false);
                $('#cancelar').attr('disabled', false);
                $('#avancar').attr('disabled', false);
                $('#voltar').attr('disabled', false);
            
            }
            
            
            function _mostra2 () {
                
                $('.input-large').attr('disabled', true);
                $('. btn btn-mini').attr('disabled', false);
                $('#salvar').attr('disabled', true);
                $('#cancelar').attr('disabled', true);
            
            }
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
                   <h1 class="page-header">Relatórios</h1>
                     
                </div>
                
                    
               
                   
                     
               
            </div>
            
            
            
            <div class="row">
                <div class="col-lg-offset-3 col-lg-6">
                    
                    <div class="panel panel-default">
                        
                        <div class="panel-heading">
                            Relátorio Cadastrados
                        </div>
                        
                        
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            
                              
                            
                             <ul class="pager">
                                            <button class="btn btn-primary" onClick="_mostraDivi();">Pesquisar por Dia</button>
                                             <button class="btn btn-primary" onClick="_mostraDivi2();">Pesquisar por Período</button>
						
                                             <br><br><br>
                                </ul>
                                    
					<div id="dia" class="widget-box" style="display: block">
                                            <form target="_blank" class="form-signin" action="../PDF/pdfCadastrado.php" method="post" >
                                            
						
						
                                                <div class="widget-content">
							
         
                                                    <div class="row-fluid">
							<div class="col-lg-offset-2 col-lg-7">
								
                                                                
                                                            <div class="controls-row">
                                                                <div class="input-prepend">


                                                                     <div class="row-fluid">
                                                                            <label for="id-date-picker-1">Dia</label>
                                                                        </div>
                                                                    <div class="form-group input-group">
                                                                        <input type="text" class="form-control date-picker" id="id-date-picker-3" type="text" data-date-format="yyyy-mm-dd" name="txtdia">
                                                                        <span class="input-group-btn">
                                                                            <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i>
                                                                            </button>
                                                                        </span>
                                                                    </div>

                                                                

                                                                  
                                                                      </div>
                                                                 </div>
                                                                
                                                            
                                                            <br><br>
                                                            <ul class="pager">
                                                                <button class="btn btn-danger" name="cadastrar" value="Cadastrar" id="avancar" type="submit" onClick="_mostraDivi()" >Gerar Relátorio <i class="fa fa-file-text"></i></button>
                                                   
                                                            </ul>
							</div>
							
                                                        
						
                                                        
                                                    </div>
                                                    
                                                    
                                                    
                                                    </div>
                                                
                                                 </form>
                                                          						
                                        </div>
                            
                            
                            
                            <div id="periodo" class="widget-box" style="display: none">
                                            <form target="_blank" class="form-signin" action="../PDF/pdfCadastrado2.php" method="post" >
                                            
						
						
                                                <div class="widget-content">
							
         
                                                    <div class="row-fluid">
							<div class="col-lg-offset-2 col-lg-7">
								
                                                                
                                                            <div class="controls-row">
                                                                <div class="input-prepend">


                                                                    <div class="row-fluid">
                                                                            <label for="id-date-picker-1">Período</label>
                                                                        </div>

                                                                    <div class="form-group input-group">
                                                                        <input type="text" class="form-control date-picker" id="id-date-picker-1" type="text" data-date-format="yyyy-mm-dd" name="txtdia1">
                                                                        <span class="input-group-btn">
                                                                            <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i>
                                                                            </button>
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    
                                                                     <div class="row-fluid">
                                                                            <label for="id-date-picker-1">Período</label>
                                                                        </div>
                                                                    
                                                                     <div class="form-group input-group">
                                                                        <input type="text" class="form-control date-picker" id="id-date-picker-2" type="text" data-date-format="yyyy-mm-dd" name="txtdia2">
                                                                        <span class="input-group-btn">
                                                                            <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i>
                                                                            </button>
                                                                        </span>
                                                                    </div>

                                                                

                                                                  
                                                                      </div>
                                                                 </div>
                                                                
                                                            
                                                            <br><br>
                                                            <ul class="pager">
                                                                <button class="btn btn-danger" name="cadastrar" value="Cadastrar" id="avancar" type="submit" onClick="_mostraDivi()" >Gerar Relátorio <i class="fa fa-file-text"></i></button>
                                                   
                                                            </ul>
							</div>
							
                                                        
						
                                                        
                                                    </div>
                                                    
                                                    
                                                    
                                                    </div>
                                                
                                                 </form>
                                                          						
                                        </div>
                                    
                        
                            
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
