<?php

/*
 * Este arquivo implementa a camada de persistencia
 *   para o banco de dados MySQL.
 */
		
/* Faz a conex�o com o SGBD */
function conectarSGBD($host, $login, $senha, $bancoDados ){
    $a = mysqli_connect($host, $login, $senha, $bancoDados) or die ("Falha na conexão com o SGBD. Host = $host");
    return $a;
}

/* Desfaz a conex�o com o SGBD */
function disconectarSGBD($link){
    mysqli_close($link);
}

/* Seleciona o Banco de Dados */
function selecionarBD($link, $bancoDados){
    mysqli_select_db($link, $bancoDados) or die ("Falha na seleção do banco: $bancoDados");
}

function inserirBD($link, $sql){
    mysqli_query($link, $sql) or die("Falha na execução da consulta: $sql");
}

function alterarBD($link, $sql){
    mysqli_query($link, $sql) or die("Falha na execução da consulta: $sql");
}

function excluirBD($link, $sql){
    mysqli_query($link, $sql) or die("Falha na execução da consulta: $sql");
}

function pesquisarBD($link, $sql){
    $rs = mysqli_query($link, $sql) or die("Falha na execução da consulta: $sql");
    return $rs;
}

/* Retorna o último índice inserido de um campo autoincremento */
function obterUltimoIndice($link){
    $a = mysqli_insert_id($link);
    return $a;
}

/* Retorna a linha na qual o marcador do resultado da consulta est� apontando */
function obterLinha($result_set){
    $a = mysqli_fetch_assoc($result_set);
    return $a;
}

/* Retorna o n�mero de linhas do resultado da consulta */
function obterNumLinhas($result_set){
    $a = mysqli_num_rows($result_set);
    return $a;
}

/* Posiciona o marcador na linha especificada pela posi��o */
function posicionarLinha($result_set, $pos){
    $a = mysqli_data_seek($result_set, $pos);
    return $a;
}

?>
