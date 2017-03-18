<html>
<head>
<title>Entidade controle para <?php echo $this->obj->metadata()->getClassname(); ?></title>
</head>
<body>
<h1>Entidade controle para <?php echo $this->obj->metadata()->getClassname(); ?></h1>

<p>Editar outra entidade</p>
<select onchange="location.href = this.value">
<option value="">-- Selecione </option>
<?php
$files = glob('*.php');

foreach($files as $file)
{
	$list = explode('.', $file);
	echo '<option value="'.$file.'">' . array_shift($list).'</option>'.PHP_EOL;
}

?>
</select>