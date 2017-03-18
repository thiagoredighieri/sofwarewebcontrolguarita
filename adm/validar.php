
<?php
session_start();

if (  $_SESSION['tipo'] != "A") {
    //Destrói
    session_destroy();
 
    //Limpa
    unset ($_SESSION['login']);
    unset ($_SESSION['senha']);
	unset ($_SESSION['tipo']);
     
    //Redireciona para a página de autenticação
    header('location:../login.php?erro=Você tem permissão para acessar esse conteúdo');
}



if ( !isset($_SESSION['login']) and !isset($_SESSION['senha'])) {
    //Destrói
    session_destroy();
 
    //Limpa
    unset ($_SESSION['login']);
    unset ($_SESSION['senha']);
	unset ($_SESSION['tipo']);
     
    //Redireciona para a página de autenticação
    header('location:../login.php?erro=Você tem permissão para acessar esse conteúdo');
}



?>