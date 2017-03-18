
<?php
// Conectar no banco de dados
require_once('conecta.php');

// Dados vindos pela url
$id = $_GET['id'];
$email = $_GET['email'];


//Buscar os dados no banco
$sql = "select * from usuario where idUsuario = '$id'";
$sql = mysqli_query($con, $sql);
$rs = mysqli_fetch_array($con, $sql);

// Comparar os dados que pegamos no banco, com os dados vindos pela url
$valido = true;

if( $email !== $rs['email']  )
    $valido = false;

// Os dados estÃ£o corretos, hora de ativar o cadastro
if( $valido === true ) {
    $sql = "update usuario set estadoCadastro='ativo' where idUsuario='$id'";
    mysql_query( $sql );
    echo "Cadastro ativado com sucesso!";
} else {
    echo "Informacoes invalidas";
}





?>

	
	