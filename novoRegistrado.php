<?php
require 'conecta.php';


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
$motivo = $_POST["ano"];



 $sql = "INSERT INTO categorias (nome) 
		VALUES ('$nome')";
		
	
    if (mysqli_query($con,$sql)) {
       
        header('Location: ../consultaCategoria.php');
        
    } else {
		
        echo "erro: " . mysqli_error();
    }


?>
