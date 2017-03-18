
<?php

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
	if ($retorno > 0)
            header("Location:resposta.php?msg=1");
	else
	    header("Location:resposta.php?msg=2");
  
  ?>
	