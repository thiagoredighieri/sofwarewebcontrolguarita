<?php
function cortaTexto($txt, $limit = 60){
	$txt = str_replace('<','&lt;',$txt);
	if(strlen($txt) > $limit){
		return substr($txt, 0, $limit).'...';
	} else {
		return $txt;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lumine - Controle Básico - <?php echo $obj->metadata()->getClassname(); ?></title>
<script type="text/javascript" src="ui/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="ui/js/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="jquery.mask.js"></script>
<script type="text/javascript">
$(function(){
	$('.datetime,.timestamp').datepicker({dateFormat: 'yy-mm-dd 00:00:00', showOn: 'button', buttonImage: 'calendar.png', buttonImageOnly: true});
	$('.date').mask('9999-99-99').width(80).datepicker({dateFormat: 'yy-mm-dd', showOn: 'button', buttonImage: 'calendar.png', buttonImageOnly: true});;
	$('.datetime,.timestamp').mask('9999-99-99 99:99:99').width(130);
	$('.time').mask('99:99:99').width(80);
	$('#jumpselect').change(function(){
		location.href = this.value;
	});

	setTimeout(function(){
		$('.sucesso').hide('fast');
	}, 2000);
});
</script>
<link href="styles.css" rel="stylesheet" type="text/css" />
<link href="ui/css/cupertino/jquery.css" rel="stylesheet" type="text/css" />
</head>

<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" id="jumpmenu" style="text-align:right">
    <select id="jumpselect" name="jumpselect">
        <option value="">[ Ir para o controle ]</option>
		<?php
        $dh = opendir($obj->_getConfiguration()->getProperty('class_path') . '/controls');
		while(($file=readdir($dh)) !== false){
			if(preg_match('@\.php$@i',$file)){
				printf('<option value="%s">%s</option>', $file, str_replace('.php','',$file));
			}
		}
		closedir($dh);
        ?>
    </select>
    <hr />
</form>

<h1>Gerenciamento para [ <?php echo $obj->metadata()->getClassname(); ?> ]
<em>Lumine - Engenharia Reversa</em>
</h1>

<?php if(!empty($_GET['msg']) && $_GET['msg'] == 'ok'): ?>
<div class="sucesso">
	Operações efetuadas com sucesso!
</div>
<?php endif; ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" id="edicao">
  <table width="100%" border="0" cellspacing="1" cellpadding="2" class="tableWhite">
	<?php
    $parts = $obj->metadata()->getFields();

    foreach($parts as $name => $item):
		$item['name'] = $name;
    ?>
    <tr>
      <td width="23%"><?php echo ucfirst($item['name']) . (!empty($item['options']['notnull']) ? ' <span class="obrigatorio">*</span>' : ''); ?></td>
      <td width="77%">
		<?php
		if(empty($item['options']['autoincrement']) ){
			if( empty($item['options']['foreign']) ){
				switch($item['type']){
					case 'text':
					case 'longtext':
					case 'mediuntext':
					case 'blob':
					case 'longblob':
					case 'mediumblob':
						echo '<textarea name="', $item['name'], '" id="', $item['name'], '">',
							(isset($_POST[$item['name']]) ? $_POST[$item['name']] : ''),
							'</textarea>';
					break;
					
					case 'boolean':
						$val = isset($_POST[$name]) ? $_POST[$name] : (isset($item['options']['default']) ? $item['options']['default'] : '');
						
						echo '<select name="' . $name . '">';
						echo '<option value="1"' . ($val==1 || $val=='t' ? ' selected="selected"' : '') . '>Sim</option>';
						echo '<option value="0"' . ($val==0 || $val=='f' ? ' selected="selected"' : '') . '>Não</option>';
						echo '</select>';
						
					break;
					
					default:
						echo '<input type="text" class="textinput ', $item['type'], '" name="', $item['name'], '" id="', $item['name'], '" ',
							(!empty($item['length']) ? ' size="' . min(50,$item['length']) . '"' : ''),
							(!empty($item['length']) ? ' maxlength="' . $item['length'] . '"' : ''),
							' value="', (isset($_POST[$item['name']]) ? $_POST[$item['name']] : (isset($item['options']['default']) ? $item['options']['default'] : '') ), '" />';
					break;
				}
			} else {
				$display = empty($item['options']['displayField']) ? $item['options']['linkOn'] : $item['options']['displayField'];
				echo '<select class="foreign" id="', $item['name'], '" name="', $item['name'], '">';
				echo '<option value=""></option>';
				echo Lumine_Util::buildOptions($obj->_getConfiguration()->getProperty('package').'.'.$item['options']['class'], $item['options']['linkOn'], $display, isset($_POST[$item['name']]) ? $_POST[$item['name']] : '');
				echo '</select>';
			}
			
			// opcao para marcar o campo como nulo
			printf('<input type="checkbox" value="1" name="_null_[%s]" title="Marcar como nulo" /> ', $item['name']);
		} else {
			echo '[ auto-increment ]';
		}
        ?>
      </td>
    </tr>
	<?php endforeach; ?>
	<?php 
	$relations = $obj->metadata()->getRelations();
	
	foreach($relations as $name => $item):
		$item['name'] = $name;
		if($item['type'] != Lumine_Metadata::MANY_TO_MANY){
			continue;
		}
	?>
	<tr>
	   <td><?php echo ucfirst($item['name']); ?></td>
	   <td>
	   <div class="containerElementos">
		<?php
		$obj->_getConfiguration()->import($item['class']);
		$ref = new ReflectionClass($item['class']);
		$inst = $ref->newInstance();
		$inst->find();
		
		$pkfs = $inst->metadata()->getPrimaryKeys();
		$key = $pkfs[0];
		
		while($inst->fetch()){
			printf('<input type="checkbox" name="%s[]" value="%s" %s /> %s<br>',
				$item['name'],
				$inst->$key['name'],
				!empty($_POST[$name]) && in_array($inst->$key['name'], $_POST[$name]) ? 'checked="checked"' : '',
				$inst->nome == '' ? ($inst->descricao == '' ? $inst->$item['linkOn'] : $inst->descricao) : $inst->nome
			);
		}
		
		?>
		</div>
	   </td>
	</tr>
	<?php endforeach; ?>
    <tr>
      <td>
	<?php
	////////////////// CHAVES PRIMARIAS //////////////////////
	$pks = $obj->metadata()->getPrimaryKeys();
	foreach($pks as $pk){
		printf('<input type="hidden" name="_pk_%s" id="_pk_%s" value="%s" />'.PHP_EOL,
			$pk['name'],
			$pk['name'],
			empty($_POST[$pk['name']]) ? '' : $_POST[$pk['name']]
		);
		
		if(!empty($pk['options']['autoincrement'])){
			printf('<input type="hidden" name="%s" id="%s" value="%s" />'.PHP_EOL,
				$pk['name'],
				$pk['name'],
				empty($_POST[$pk['name']]) ? '' : $_POST[$pk['name']]
			);
		}
	}
	?>
    <input name="_lumineAction" id="acao" type="hidden" value="" />
      </td>
      <td>
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button"><span>Cancelar</span></a>
        <a href="javascript:" onclick="$('#acao').val('save');$('#edicao').submit();" class="button"><span>Salvar</span></a>
      </td>
    </tr>
  </table>
</form>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" enctype="multipart/form-data" id="busca">
<?php  
$formAction = $_SERVER['PHP_SELF'];

$obj->reset();

// indice 
$idx = 1;
// unindo as classes
foreach($parts as $name => $item){
	if(!empty($item['options']['class'])){
		$obj->_getConfiguration()->import($item['options']['class']);
		$obj->join(new $item['options']['class'], 'left', 'o'.($idx++));
	}
}

$obj->selectAs();

// indice 
$idx = 1;
// buscando os campos
foreach($parts as $name => $item){
	if(!empty($item['options']['class'])){
		$field = !empty($item['options']['displayField']) ? $item['options']['displayField'] : $item['options']['linkOn'];
		$obj->_getConfiguration()->import($item['options']['class']);
		$obj->select('o'.($idx++) . '.' . $field . ' as ' . $name);
	}
}


// vamos aplicar os filtros
foreach($_GET as $key => $value){
	if($value == ''){
		continue;
	}
	
	if(preg_match('@^_filter_@', $key)){
		$fieldname = str_replace('_filter_', '', $key);
		$field = $obj->metadata()->getField($fieldname);
		
		switch($field['type']){
			case 'varchar':
			case 'char':
			case 'tinychar':
			case 'text':
			case 'longtext':
			case 'mediuntext':
			case 'blob':
			case 'longblob':
			case 'mediumblob':
				$obj->where('{' . $fieldname . '} like ?', $value);
			break;
			
			default:
				$obj->where('{' . $fieldname . '} = ?', $value);
			break;
		}
	}
}

// chaves primarias
$pks = $obj->metadata()->getPrimaryKeys();
$string = '';

foreach($pks as $name => $pk){
	$string .= '{'.$pk['name'].'}, ';
}

$string = trim($string, ', ');

$total = $obj->count($string == '' ? '*' : 'distinct ' . $string);
$limit = empty($_GET['limit']) ? 0 : (int)$_GET['limit'];
$offset = empty($_GET['offset']) ? 0 : (int)$_GET['offset'];

if($limit <= 0) {
	$limit = 10;
}

$obj->limit($offset, $limit);

if( $obj->_getConfiguration()->getProperty('dialect') == 'MySQL'){
	$obj->group($string);
}

$obj->find();
?>

<h1>Listagens de itens para [ <?php echo $obj->metadata()->getClassname(); ?> ]</h1>
<table width="100%" border="0" cellspacing="1" cellpadding="2" class="tableWhite">
 <thead>
   <tr>
       <th width="3%">&nbsp;</th>
       <th width="3%">&nbsp;</th>
       <?php
       foreach($parts as $name => $item){
           echo '<th>',
               ucfirst($name),
               '</th>';
       }
       ?>
   </tr>
   <tr>
       <th width="3%">&nbsp;</th>
       <th width="3%"><a href="javascript:" onclick="$('#busca').submit();"><img src="magnifier.png" width="16" height="16" alt="Pesquisar" title="Pesquisar" /></a></th>
       <?php
       foreach($parts as $name => $item){
		   $item['name'] = $name;
		   if(empty($item['options']['foreign'])){
			   echo '<th>',
				   sprintf('<input type="text" class="textinput inputbusca %s" name="_filter_%s" value="%s" />',
						$item['type'],
						$name,
						!isset($_GET['_filter_'.$name]) ? '' : $_GET['_filter_'.$name]
					),
				   '</th>';
		   } else {
				$display = empty($item['options']['displayField']) ? $item['options']['linkOn'] : $item['options']['displayField'];
				echo '<th>';
				echo '<select class="foreign" name="_filter_', $item['name'], '">';
				echo '<option value=""></option>';
				echo Lumine_Util::buildOptions($obj->_getConfiguration()->getProperty('package').'.'.$item['options']['class'], $item['options']['linkOn'], $display, isset($_GET['_filter_'.$item['name']]) ? $_GET['_filter_'.$item['name']] : '');
				echo '</select>';
				echo '</th>';
		   }
		}
       ?>
   </tr>
 </thead>
 <tbody>
<?php
	while($obj->fetch()):
		$pks_string = '';
		$pks = $obj->metadata()->getPrimaryKeys();
		$vals = array();
		
		foreach($pks as $name => $prop){
			$vals[] = '_pk_'.$prop['name'].'='.urlencode($obj->$prop['name']);
		}
		
		$pks_string = implode('&amp;', $vals);
			
?>
	<tr>
		<td><a href="?_lumineAction=edit&amp;<?php echo $pks_string; ?>"><img src="page_edit.png" width="16" height="16" alt="Editar" title="Editar" /></a></td>
		<td><a href="?_lumineAction=delete&amp;<?php echo $pks_string; ?>"><img src="page_delete.png" width="16" height="16" alt="Remover" title="Remover" /></a></td>
		<?php
        foreach($parts as $name => $item){
           echo '<td>',
               (is_null($obj->$name) ? '<em>null</em>' : cortaTexto($obj->$name)),
               '</td>';
        }
        ?>
	</tr>
 <?php endwhile; ?>
 </tbody>
</table>
<br />
Registros encontrados: <?php echo $total; ?><br />
<br />
<select id="__limit" name="limit" onchange="$('#acao').val('');$('#busca').submit()">
  <?php
$max   = 70;
$min   = 5;
$step  = 5;

for($i=$min; $i<=$max; $i += $step)
{
	printf('<option value="%d"%s>Mostrar %s registros por página</option>'.PHP_EOL, $i, $i==$limit ? ' selected':'', $i);
}

?>
</select>
<select id="_paginacao" onchange="location.href=this.value">
  <?php
$paginas   = ceil($total/$limit);
$offset    = (int)@$_GET['offset'];

for($i=0; $i<$paginas; $i++)
{
	$lnk = $formAction . '?';
	foreach($_GET as $k => $v)
	{
		if($k == 'offset')
		{
			continue;
		}
		$lnk .= $k .'=' .$v. '&';
	}
	$lnk .= 'offset=' . ($i * $limit);
	printf('<option value="%s"%s>%s</option>', $lnk, $offset == $i * $limit ? ' selected' : '', 'Página ' . ($i + 1) .' de '. $paginas);
}

?>
</select>
</form>


</body>
</html>