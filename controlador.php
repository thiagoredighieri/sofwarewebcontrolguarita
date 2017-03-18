<?php

// Inclusão dos arquivos da camada do modelo
include_once("modelo.php");

// Obtem a operacao passada pelos arquivos da camada de visão
if ( isset($_POST["operacao"]) ) {
    $operacao = $_POST["operacao"];
} else if ( isset($_GET["operacao"]) ) {
    $operacao = $_GET["operacao"];
}
/* 
 *  Verifica qual operacao tratar
 */
if ($operacao == "validarUsuario"){
        // Recebe cada campo do formulario e coloca em uma variavel.

	// Recebe cada campo do formulário e coloca em uma variável.
		$login2 = $_POST['login'];
		$senha2 = $_POST['senha'];

        // Chama o metodo de verificar login e senha implementado em modelo.php
	$retorno = validarUsuario($login2, $senha2);

	// Com base no retorno da operação, devolve a mensagem ou redireciona para outra página
	if ($retorno == SUCESSO_LOGIN)
		
			if ($_SESSION["tipo"]=='A'){
				header("Location:adm/index.php");
			}else{
				
				header("Location:guarita/index.php");
			}
			
            
	else
	    header("Location:login.php?erro=Login e senha inválidos!");


} else if ($operacao == "registrado") {
	
	
				$nome = $_POST["nome"];
				$endereco = $_POST["endereco"];
				$cep = $_POST["cep"];
				$email = $_POST["email"];
				$tel = $_POST["tel"];
				$numero = $_POST["numero"];
				$cidade = $_POST["cidade"];
				$estado = $_POST["estado"];


				$marca = $_POST["marca"];
				$cor = $_POST["cor"];
				$placa = $_POST["placa"];
				$modelo = $_POST["modelo"];
				$ano = $_POST["ano"];

				$funcao = implode (',', $_POST["txtfuncao"]);
				$motivo = $_POST["motivo"];
					
	
	// Chama o metodo inserirParticipante implementado em modelo.php
	$endereco = inserirEndereco($endereco, $cep, $numero, $cidade, $estado);
	
	$pessoa = inserirPessoa($nome,$email,$tel);
	
	$veiculo = inserirVeiculo($marca,$cor,$placa,$modelo, $ano);
	
	$retorno = inserirRegistrado($funcao,$motivo, $endereco, $pessoa, $veiculo);
	
	$id=$retorno;
	
	// Inclui o arquivo class.phpmailer.php localizado na pasta phpmailer
		require 'phpmailer/PHPMailerAutoload.php';
		// Inicia a classe PHPMailer
		$mail = new PHPMailer();
		// Define os dados do servidor e tipo de conexão
		// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$mail->IsSMTP(); // Define que a mensagem será SMTP
		$mail->Host = "mx1.hostinger.com.br"; // Endereço do servidor SMTP
		//$mail->SMTPAuth = true; // Usa autenticação SMTP? (opcional)
		//$mail->Username = 'seumail@dominio.net'; // Usuário do servidor SMTP
		//$mail->Password = 'senha'; // Senha do servidor SMTP
		// Define o remetente
		// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$mail->From = "admsistema@aulapi.esy.es"; // Seu e-mail
		$mail->FromName = "Administrador do Sistema"; // Seu nome
		// Define os destinatário(s)
		// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$mail->AddAddress("$email");
		
		
		
		$mail->AddCC('admsistema@aulapi.esy.es', 'Admnistrador'); // Copia
		//$mail->AddBCC('fulano@dominio.com.br', 'Fulano da Silva'); // Cópia Oculta
		// Define os dados técnicos da Mensagem
		// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		$mail->IsHTML(true); // Define que o e-mail será enviado como HTML
		//$mail->CharSet = 'iso-8859-1'; // Charset da mensagem (opcional)
		// Define a mensagem (Texto e Assunto)
		// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		
		
		// Criar as variaveis para validar o email
        $url =sprintf( 'id=%s&email=%s',$id, $email);

        $mensagem = 'Para confirmar seu cadastro acesse o link:'."\n";
        $mensagem .= sprintf('http://aulapi.esy.es//ativar.php?%s',$url);
		
		
		$mail->Subject  = "Mensagem de Confirmação de cadastro"; // Assunto da mensagem
		$mail->Body = "$mensagem";
		// Define os anexos (opcional)
		// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
		//$mail->AddAttachment("c:/temp/documento.pdf", "novo_nome.pdf");  // Insere um anexo
		// Envia o e-mail
		$enviado = $mail->Send();
		// Limpa os destinatários e os anexos
		$mail->ClearAllRecipients();
		$mail->ClearAttachments();
		// Exibe uma mensagem de resultado
		if ($enviado) {
		  echo "E-mail enviado com sucesso!";
		} else {
		  echo "Não foi possível enviar o e-mail.";
		  echo "<b>Informações do erro:</b> " . $mail->ErrorInfo;
		}
	
	

	// Com base no retorno da operacao, devolve a mensagem ou redireciona para outra pagina
	//if ($retorno > 0)
           // header("Location:resposta.php?msg=1");
	//else
	  //  header("Location:resposta.php?msg=2");
	
}
	



?>
