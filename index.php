<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Controle de Guarita</title>

    <!-- Bootstrap core CSS -->
    <link href="styles/css/bootstrap.css" rel="stylesheet">

    <!-- Add custom CSS here -->
    <link href="styles/css/modern-business.css" rel="stylesheet">
    <link href="styles/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    
    <style>
        
        
            /* carousel */
.media-carousel 
{
  margin-bottom: 0;
  padding: 0 40px 30px 40px;
  margin-top: 30px;
}
/* Previous button  */
.media-carousel .carousel-control.left 
{
  left: -12px;
  background-image: none;
  background: none repeat scroll 0 0 #222222;
  border: 4px solid #FFFFFF;
  border-radius: 23px 23px 23px 23px;
  height: 40px;
  width : 40px;
  margin-top: 100px
}
/* Next button  */
.media-carousel .carousel-control.right 
{
  right: -12px !important;
  background-image: none;
  background: none repeat scroll 0 0 #222222;
  border: 4px solid #FFFFFF;
  border-radius: 23px 23px 23px 23px;
  height: 40px;
  width : 40px;
  margin-top: 100px
}
/* Changes the position of the indicators */
.media-carousel .carousel-indicators 
{
  right: 50%;
  top: auto;
  bottom: 0px;
  margin-right: -19px;
}
/* Changes the colour of the indicators */
.media-carousel .carousel-indicators li 
{
  background: #c0c0c0;
}
.media-carousel .carousel-indicators .active 
{
  background: #333333;
}
.media-carousel img
{
  width: 250px;
  height: 200px
}
/* End carousel */


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

h1{
    
     color: #fcfdfd;
    
}
   
    </style>
    
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!-- You'll want to use a responsive image option so this logo looks good on devices - I recommend using something like retina.js (do a quick Google search for it and you'll find it) -->
          <a class="navbar-brand" href="index.html"></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-ex1-collapse">
          <ul class="nav navbar-nav navbar-right">
           
            <li><a class=" btn-link" target="_blank" href="login.php?errn=">Acesso</a></li>
           
            
              
            
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container -->
    </nav>
    
    <div id="myCarousel" class="carousel slide">
      <!-- Indicators -->
        <ol class="carousel-indicators">
          <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
          <li data-target="#myCarousel" data-slide-to="1"></li>
          <li data-target="#myCarousel" data-slide-to="2"></li>
        </ol>

        <!-- Wrapper for slides -->
        <div class="carousel-inner">
          <div class="item active">
            <div class="fill" style="background-image:url('img/slide-03.jpg');"></div>
            <div class="carousel-caption">
              <h1>Ainda não cadastrou seu veículo?</h1><a  href="#myModal" role="button" class="btn btn-large btn-primary"  data-toggle="modal" >Cadastre-se Aqui</a>
            </div>
          </div>
          <div class="item">
            <div class="fill" style="background-image:url('img/slide-03.jpg');"></div>
            <div class="carousel-caption">
              <h1>Avisos <a href="http://st.ifes.edu.br">http://st.ifes.edu.br</a></h1>
            </div>
          </div>
          <div class="item">
            <div class="fill" style="background-image:url('img/slide-03.jpg');"></div>
            <div class="carousel-caption">
              <h1>Site Oficial <a href="http://st.ifes.edu.br">http://st.ifes.edu.br</a></h1>
            </div>
          </div>
		  
		
        </div>

        <!-- Controls -->
        <a class="left carousel-control" href="#myCarousel" data-slide="prev">
          <span class="icon-prev"></span>
        </a>
        <a class="right carousel-control" href="#myCarousel" data-slide="next">
          <span class="icon-next"></span>
        </a>
    </div>

    

    <div class="section text-center">

      <div class="container">

        <div class="row">
          
            


            <div class="text-center">

                <br>
                <div class="col-md-offset-2 col-md-12">
                    
                    
                    
                 

                    
                </div>

            </div>

            
            
            
        </div><!-- /.row -->

      </div><!-- /.container -->

    </div><!-- /.section-colored -->

    
    

    
    
     <div class="container">
      
      
      <!-- Service Paragraphs -->

      <div class="row">

        <div class="col-md-8">
          <h2 class="page-header">Controle de Guaritas</h2>
            <p>O Localiza surgiu a partir da necessidade de se atuometizar o controle de entrada e saída de veículos do campus Santa Teresa. 
                        Este controle que era feito manualmente e não atendia as expectativas dos usuários e da reitoria do campus, 
                        que buscava um controle automatizado que facilitaria o controle e acompanhamento desta atividade no campus.
                        O Sistema é capaz de gerar relatórios de trafégo de veículos diário, mensal e anual. 
			Realiza o cadastro de veículos de professores, alunos e funcionários do campus, além de controlar a entrada de veículos 
			externos que trafegam dentro do campus.</p>
        </div>

        
           
          
      </div><!-- /.row -->

      <!-- Service Tabs -->

       <hr>
      <div class="row">

        <div class="col-md-8">
          <h2 class="page-header">Ifes Campus Santa Teresa</h2>
          <ul id="myTab" class="nav nav-tabs">
            <li class="active"><a href="#service-one" data-toggle="tab">Fotos</a></li>
            
            
          </ul>
          <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade in active" id="service-one">
              
                <div class="container">

                    <div class='row'>
                        <div class='col-md-8'>
                            <div class="carousel slide media-carousel" id="media">
                                <div class="carousel-inner">
                                    <div class="item  active">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <a class="thumbnail" href="#"><img alt="" src="img/fotos1.jpg"></a>
                                            </div>          
                                            <div class="col-md-4">
                                                <a class="thumbnail" href="#"><img alt="" src="img/fotos2.jpg"></a>
                                            </div>
                                            <div class="col-md-4">
                                                <a class="thumbnail" href="#"><img alt="" src="img/fotos3.jpg"></a>
                                            </div> 
                                            
                                            
                                        </div>
                                    </div>
                                    
                                    <div class="item">
                                        <div class="row">
                                           <div class="col-md-4">
                                                <a class="thumbnail" href="#"><img alt="" src="img/fotos4.jpg"></a>
                                            </div>          
                                            <div class="col-md-4">
                                                <a class="thumbnail" href="#"><img alt="" src="img/fotos2.jpg"></a>
                                            </div>
                                            <div class="col-md-4">
                                                <a class="thumbnail" href="#"><img alt="" src="img/fotos3.jpg"></a>
                                            </div>       
                                        </div>
                                    </div>
                                </div>
                                <a data-slide="prev" href="#media" class="left carousel-control">‹</a>
                                <a data-slide="next" href="#media" class="right carousel-control">›</a>
                            </div>                          
                        </div>
                    </div>
                </div>
            
            
            </div>
           
          
          </div>
        </div>
          
           <div class="col-md-4">
          <h2 class="page-header">Visite a Página Oficial</h2>
            <p>Fique por dentro de tudo o que acontece no nosso Campus</p>
            <a class="btn btn-success" href="#">Visitar</a>
        </div>

      </div><!-- /.row -->

      <!-- Service Images -->

      
       <hr>
      
      <div class="row">
          
          <div class="col-sm-8">
          <h2 class="page-header">Contato</h2>
          <p>Tire suas dúvidas</p>
			
            <form  method="POST" action="enviarEmail.php">
	            <div class="row">
	              <div class="form-group col-lg-4">
	                <label for="input1">Nome</label>
	                <input type="text" name="fale[nome]" class="form-control" id="input1">
	              </div>
	              <div class="form-group col-lg-4">
	                <label for="input2">Email </label>
	                <input type="email" name="fale[email]" class="form-control" id="input2">
	              </div>
	              <div class="form-group col-lg-4">
                          <label for="input3">Assunto</label>
	                <input type="text" name="fale[assunto]" class="form-control" id="input3">
	              </div>
	              <div class="clearfix"></div>
	              <div class="form-group col-lg-12">
	                <label for="input4">Mensagem</label>
	                <textarea name="fale[mensagem]" class="form-control" rows="6" id="input4"></textarea>
	              </div>
	              <div class="form-group col-lg-12">
	                <input type="hidden" name="save" value="contact">
	                <button type="submit" class="btn btn-primary">Enviar</button>
	              </div>
              </div>
            </form>
        </div>

        <div class="col-sm-4">
          <h3>IFES Santa Teresa</h3>
          
          <p>
            Rodovia alguma coisas<br>
           São João de Petropolis, Santa Teresa - Es<br>
          </p>
          <p><i class="fa fa-phone"></i> <abbr title="Phone">P</abbr>: (27) 3259-1878</p>
          <p><i class="fa fa-envelope-o"></i> <abbr title="Email">E</abbr>: <a href="mailto:feedback@startbootstrap.com">feedback@startbootstrap.com</a></p>
          <p><i class="fa fa-clock-o"></i> <abbr title="Hours">H</abbr>: Segunda - Sexta: 9:00 AM to 5:00 PM</p>
          <ul class="list-unstyled list-inline list-social-icons">
            <li class="tooltip-social facebook-link"><a href="https://www.facebook.com/IFESSantaTeresa" data-toggle="tooltip" data-placement="top" title="Facebook"><i class="fa fa-facebook-square fa-2x"></i></a></li>
         
          </ul>
        </div>

          
      </div>
      

      <hr>
      
    </div><!-- /.container -->
    
    

   

    <div class="container">    
  <div class="row">
    <div class="col-lg-12">
     
      
      <div class="col-md-4">
        <ul class="list-unstyled">
          <li>Serviços<li>
          <li><a href="#">Avisos</a></li>
          <li><a href="#">Campus Santa Teresa</a></li>
          <li><a href="#">Cadastra-se Aqui</a></li>
                       
        </ul>
      </div>
      <div class="col-md-4">
        <ul class="list-unstyled">
          <li>Documentation<li>
          <li><a href="#">Manual</a></li>
          <li><a href="#">Formulario 1</a></li>
          <li><a href="#">Formulario 2</a></li>
                     
        </ul>
      </div>  
    </div>
  </div>
  <hr>
  <div class="row">
    <div class="col-lg-12">
      <div class="col-md-8">
        <a href="#">Termos de Serviço</a>    
        <a href="#">Privacidade</a>    
        <a href="#">Segurança</a>
      </div>
      <div class="col-md-4">
        <p class="muted pull-right">© 2015 Ilt. Todos Direitos Resrvados</p>
      </div>
    </div>
  </div>
</div>
    
    
    
    <div class="row">
          
            

            
                 <div class="container">
                <div class="row">
                        <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Ficha de Cadastro</h3>
        </div>
        <div class="modal-body">
            
               
            
            <form class="valida"  action="controlador.php" method="post">
			
			<input type="text" name="operacao" value= "registrado" style="display: none"/>
                
                <fieldset>
               

                    <legend>Dados pessoais</legend>

                     <div class="col-lg-6" > 
                         
                         
                         
                         <div class="form-group col-lg-9">
                                            <label>Nome Completo</label>
                                            <input class="form-control"
                                                   type="text" 
                                                    required
                                                    name="nome"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                   
                    

                         
                   <br><br><br><br>
                    
                    
                    
                   
                   
                    <div class="form-group col-lg-9">
                                            <label>Endereço</label>
                                            <input class="form-control"
                                                   type="text" 
                                                    required
                                                    name="endereco"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                   
                   
                     
                     
                   
                   <div class="form-group col-lg-4">
                                            <label>CEP</label>
                                            <input class="form-control" id="cep" data-mask="99999-999"
                                                   type="text" 
                                                    required
                                                    name="cep"
                                                   
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                   

               
                    </div>
                    
                    
                     <div class="col-lg-6" >  
                         
                   
                        
                         
                         <div class="form-group col-lg-7">
                                            <label>Email</label>
                                            <input class="form-control" 
                                                   type="email" 
                                                    required
                                                    name="email"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                  
                        
                          <div class="form-group col-lg-5">
                                            <label>Telefone</label>
                                            <input class="form-control" data-mask="(99)-99999-9999"
                                                   type="tel" 
                                                    required
                                                    name="tel"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                         
                         
                    
                   
                 
                         
                  
                    
                     <div class="form-group col-lg-4">
                                            <label>Número</label>
                                            <input class="form-control" 
                                                   
                                                    required
                                                    name="numero"
                                                   
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                    
                    
                    
                         
                          <div class="form-group col-lg-8">
                                            <label>Cidade</label>
                                            <input class="form-control"
                                                   type="text" 
                                                    required
                                                    name="cidade"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                    
                         
                         
                          <div class="form-group col-lg-4">
                                            <label>Estado</label>
                                            <input class="form-control" 
                                                    
                                                    required
                                                    name="estado"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
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
                                                    name="marca"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                    
                  
                    
                        
                         <div class="form-group col-lg-8">
                                            <label>Cor</label>
                                            <input class="form-control" 
                                                    
                                                    required
                                                    name="cor"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                        
                   
                        
                      
                        
                        
                        <div class="form-group col-lg-6">
                                            <label>Placa</label>
                                            <input class="form-control" 
                                                    data-mask="aaa-9999"
                                                    required
                                                    name="placa"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                   
                        </div>
                   
                   
                    <div class="col-lg-6" > 
                  
                    
                    
                        
                        
                        
                        
                        <div class="form-group col-lg-8">
                                            <label>Modelo</label>
                                            <input class="form-control" 
                                                 
                                                    required
                                                    name="modelo"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>
                    
                      
                        
                        
                        <div class="form-group col-lg-6">
                                            <label>Ano</label>
                                            <input class="form-control" 
                                                   
                                                    required
                                                    data-mask="9999"
                                                    name="ano"
                                                    
                                                    data-validation-required-message="Preencha este campo">
                                                     <p class="help-block"></p>
                         </div>

                        
                        </div>
                    
               </fieldset>
                
                
                <fieldset>
                   <legend>Vínculo com a Instituição</legend>

                    
                    <div class="form-group col-lg-6">
                                            
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="servidor" name="txtfuncao[]">Servidor Ativo
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="servidorI" name="txtfuncao[]">Servidor Inativo
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="servico" name="txtfuncao[]">Prestador de Serviço
                                                </label>
                                            </div>
                                             <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="estagiario" name="txtfuncao[]">Estágiario
                                                </label>
                                            </div>
                                             <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="aluno" id="al"  name="txtfuncao[]" onclick="verificaEmpr(this);">Aluno
                                                </label>
                                                 
                                                 <div class="form-group"  style="display: none" id="dival">
                                                  
                                                    <input class=" form-control" 
                                                           placeholder="Curso"
                                                           
                                                           name="txtfuncao[]"
                                                          
                                                           data-validation-required-message="Preencha este campo">
                                                    <p class="help-block"></p>
                                                </div>
                                                 
                                            </div>
                                            
                                        </div>
                   
               </fieldset>
                
                
                <fieldset>
                   <legend>Motivo da Solicitação</legend>

                   
                   <div class="form-group col-lg-6">
                                            
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="motivo" id="optionsRadios1" value="trabalho" checked>Deslocamento Casa/Trabalho/Casa
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="motivo" id="optionsRadios2" value="estudo">Deslocamento Casa/Estudo/Casa
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="motivo" id="optionsRadios3" value="residente">Residente no Campus
                                                </label>
                                            </div>
                                            
                                            
                   </div>
                   
                  
                   
               </fieldset>

                <br>
                
                
                <input type="submit" class="btn btn-primary">
                
           </form>
            
                </div>
            
      
        <div class="modal-footer">
            <button class="btn btn-danger" data-dismiss="modal" aria-hidden="true">Cancelar</button>    
        </div>
                        </div>

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dalog -->
        </div><!-- /.modal -->

        
                </div>
        </div>
    
    <!-- JavaScript -->
    <script src="styles/js/jquery-1.10.2.js"></script>
    <script src="styles/js/bootstrap.js"></script>
    <script src="styles/js/modern-business.js"></script>
    <script src="styles/js/jqBootstrapValidation.js"></script>
   
    <script src="styles/js/bootstrap-inputmask.js"></script>
    <script src="styles/js/bootstrap-inputmask.min.js"></script>

  </body>
</html>
