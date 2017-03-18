<?php
// Este arquivo implementa os casos de uso do PARTICIPANTE

// Inclusão dos arquivos da camada de persistência
include_once("persistencia.php");

// Constantes utilizadas
define("COD_INVALIDO", "0");
define("SUCESSO_EXCLUIR", "1");
define("SUCESSO_LOGIN", "2");
define("LOGIN_INVALIDO", "3");

/* Esta função implementa o evento de caso de uso AUTENTICAR usuario */
function validarUsuario($login2, $senha2){
        // Verificar campos
		
        // Conexao com o servidor de banco de dados
	include("conecta.php");

        // Criacao do SQL para selecionar o usuario e a senha
       $sql = "SELECT * FROM usuario WHERE login = '$login2' AND senha = '$senha2'";

		$res = pesquisarBD($con, $sql);

        if (  $registro = obterLinha($res)  ) {
            // Login e senha conferem. Criar a sessão
			
			session_start();
	
			$_SESSION["login"] =$registro["login"];
			$_SESSION["tipo"] =$registro["tipo"];
			$_SESSION["senha"] =$registro["senha"];
			
			
			

            return SUCESSO_LOGIN;
        } else {
           return LOGIN_INVALIDO;
        }
}



function inserirEndereco($endereco, $cep, $numero, $cidade, $estado){
   // Verificar campos
   $mensagem = "";

   if ( $mensagem == "" ) {  // Não ocorreu erro
        // Conexao com o servidor de banco de dados
        include("conecta.php");
	
        // Criacao do SQL para insercao do Participante em um evento
      $sql = "INSERT INTO endereco (logradouro, cep, numero, cidade, estado) 
		VALUES ('$endereco','$cep','$numero','$cidade', '$estado')";

	// Execucao da consulta
		$resultado = inserirBD($con, $sql);
        $cod = obterUltimoIndice($con);
        return $cod;
   } else {   
      return $mensagem;			
   }
}


function inserirPessoa($nome,$email,$tel){
   // Verificar campos
   $mensagem = "";

   if ( $mensagem == "" ) {  // Não ocorreu erro
        // Conexao com o servidor de banco de dados
        include("conecta.php");
	
        // Criacao do SQL para insercao do Participante em um evento
      $sql = "INSERT INTO pessoa (nomePessoa, email, tel) 
		VALUES ('$nome','$email','$tel')";

	// Execucao da consulta
		$resultado = inserirBD($con, $sql);
        $cod = obterUltimoIndice($con);
        return $cod;
   } else {   
      return $mensagem;			
   }
}




function inserirVeiculo($marca,$cor,$placa,$modelo,$ano){
   // Verificar campos
  
   $mensagem = "";

   if ( $mensagem == "" ) {  // Não ocorreu erro
        // Conexao com o servidor de banco de dados
        include("conecta.php");
	
        // Criacao do SQL para insercao do Participante em um evento
      $sql = "INSERT INTO veiculo (marca, modelo, cor, placa, ano) 
		VALUES ('$marca','$modelo','$cor', '$placa', '$ano')";

	// Execucao da consulta
		$resultado = inserirBD($con, $sql);
        $cod = obterUltimoIndice($con);
        return $cod;
   } else {   
      return $mensagem;			
   }
}

function inserirRegistrado($funcao,$motivo, $endereco, $pessoa, $veiculo){
   // Verificar campos
  $mensagem = "";

   if ( $mensagem == "" ) {  // Não ocorreu erro
        // Conexao com o servidor de banco de dados
        include("conecta.php");
		
		$numCadastro=rand();
		$estadoCadastro="inativo";
	
        // Criacao do SQL para insercao do Participante em um evento
      $sql = "INSERT INTO registrado (motivo, especificacao, endereco_idEndereco, veiculo_idCarro, pessoa_idPessoa, num_cadastro, estadoCadastro) 
		VALUES ('$motivo','$funcao','$endereco', '$veiculo', '$pessoa', '$numCadastro', '$estadoCadastro')";

	// Execucao da consulta
		$resultado = inserirBD($con, $sql);
        $cod = obterUltimoIndice($con);
        return $cod;
   } else {   
      return $mensagem;			
   }
}




?>
