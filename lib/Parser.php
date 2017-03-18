<?php
/**
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

/**
 * Classe para fazer parses de strings
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Parser {
	/**
	 * Faz o parse de uma string 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @param string $str
	 * @param array $args
	 * @return string
	 */
	public static function parsePart(Lumine_Base $obj, $str, $args = null)
	{
		$total = strlen($str);
		$inStr = false;
		$start = '';
		$nova = '';
		$tmp = '';
		
		for($i=0; $i<$total; $i++) 
		{
			$c = substr($str, $i, 1);
			
			if($inStr == false && ($c == "'" || $c == '"'))
			{
				$tmp = self::parseEntityValues($obj, $tmp, $args);
				$nova .= $tmp;
				$tmp = '';
				$inStr = true;
				$start = $c;
				continue;
			}
			
			if($inStr == true && $c == $start)
			{
				$tmp_test = str_replace( $obj->_getConnection()->getEscapeChar() . $start, '', $c . $tmp . $c);
				if( substr_count($tmp_test, "'") % 2 == 0 )
				{
					$nova .= $start . $tmp . $start;
					$inStr = false;
					$tmp = '';
					$start = '';
					continue;
				}
			}
			
			$tmp .= $c;
		}
		
		if($tmp != '')
		{
			if($inStr == true)
			{

			}
			$nova .= self::parseEntityValues($obj, $tmp, $args);
		}
		
		return $nova;
	}
	
	/**
	 * Faz o parse de valores da entidade em um SQL
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @param string $str
	 * @param array $args
	 * @return string
	 */
	
	public static function parseEntityValues(Lumine_Base $obj, $str, $args = null){
		// pegamos todos os fields encontrados
		preg_match_all('@((\b\w+\.\w+\b)|\{(\w+\.\w+)\}|\{(\w+)\})@', $str, $fields);
		// pegamos os wildcards encontrados
		$wildcards = preg_split('@(%?\?%?|\:\w+)|.@s', $str, -1, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		// se o argumento nao for array
		if(!is_array($args)){
			$args = array($args);
		}
		
		// pegamos os objetos relacionados
		$list = $obj->_getObjectPart('_join_list');
		
		// pegamos a conexao
		$cnn = $obj->_getConnection();
		
		// armazena os campos
		$fieldList = array();
		// armazena os objetos
		$objList = array();
		
		for($i=0; $i<count($fields[0]); $i++){
			$target = null;
			$field = null;
			
			// primeiro, procurando por alias.field
			if(!empty($fields[2][$i])){
				list($alias,$fieldname) = explode('.', $fields[2][$i]);
				
				// para cada entidade relacionada
				foreach($list as $entity){
					if($entity->alias() == $alias){
						$field = $entity->metadata()->getField($fieldname);
						$target = $entity;
						break;
					}
				}
				
				
			// procurando por {Classe.campo}
			} else if(!empty($fields[3][$i])) {
				list($classname, $fieldname) = explode('.', $fields[3][$i]);
				
				// para cada entidade relacionada
				foreach($list as $entity){
					if($entity->metadata()->getClassname() == $classname){
						$field = $entity->metadata()->getField($fieldname);
						$target = $entity;
						break;
					}
				}
				
			// procurando por {campo}
			} else if(!empty($fields[4][$i])) {
				$target = $obj;
				$field = $obj->metadata()->getField($fields[4][$i]);
				
			// se nao encontrou, passa para o proximo
			} else {
				continue;
			}
			
			// armazena os resultados encontrados
			$fieldList[] = $field;
			$objList[] = $target;
		}
		
		// campo atual
		$currentField = null;
		// objeto atual
		$currentObj = null;
		
		// agora que ja temos os objetos/campos
		// vamos iterar pelos wildcards para trocar os valores
		$lastOffset = 0;
		$currentOffset = 0;
		
		for($i=0; $i<count($wildcards); $i++){
			if(count($fieldList) > 0){
				$currentField = array_shift($fieldList);
				$currentObj = array_shift($objList);
			}
			
			// se nao tem campo, dispara excecao
			if(empty($currentField)){
				throw new Exception('Nenhum campo encontrado para prepared statement');
			}
			
			// se for por binding de nome 
			if(preg_match('@:(\w+)\b@', $wildcards[$i][0], $reg)){
				$val = !isset($args[0][$reg[1]]) ? '' : $args[0][$reg[1]];
				
			// do contrario
			} else {
				$val = !array_key_exists($i, $args) ? '' : $args[$i];
				
			}
			
			$str_val = '';
			
			// se o parametro nao for um array
			if( !is_array($val) ){
				switch($wildcards[$i][0]){
					case '?%':
						$val = self::getParsedValue($currentObj, $val.'%', $currentField['type']);
					break;
					
					case '%?':
						$val = self::getParsedValue($currentObj, '%'.$val, $currentField['type']);
					break;
					
					case '%?%':
						$val = self::getParsedValue($currentObj, '%'.$val.'%', $currentField['type']);
					break;
					
					case '?':
					default:
						
						// verifica se tinha um like antes
						$part = substr($str, $lastOffset, $wildcards[$i][1] - $lastOffset);
						if(preg_match('@like@i', $part)){
							$val = self::getParsedValue($currentObj, $val, $currentField['type'], true);
							
						} else {
							$val = self::getParsedValue($currentObj, $val, $currentField['type']);
							
						}
					break;
				}
				
				$str_val = $val;
				
			// mas se for um array
			} else {
				foreach($val as $idx => $value){
					$val[$idx] = self::getParsedValue($currentObj, $value, $currentField['type']);
				}
				
				// convertemos o array para a string
				$str_val = implode(', ', $val);
			}
			
			$str = substr_replace($str, $str_val, $wildcards[$i][1], strlen($wildcards[$i][0]));
			
			for($j=$i+1; $j<count($wildcards); $j++){
				$wildcards[$j][1] += strlen($str_val) - (strlen($wildcards[$i][0]));
			}
			
			// grava a posicao do ultimo wildcard
			$lastOffset = $wildcards[$i][1];
		}
		
		return $str;
	}
	
	/**
	 * Faz o parse do valor para SQL
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base|array $obj
	 * @param mixed             $val
	 * @param string            $type
	 * @param boolean           $islike
	 * @return mixed
	 */
	public static function getParsedValue($obj, $val, $type, $islike = false, $usingDefault = false)
	{
		if( is_null( $val ) == true )
		{
			return 'NULL';
		}
		
		// se esta informando atraves de um valor padrao
		if( $usingDefault && !is_array($val) && !is_object($val) ){
			// pegamos o prefixo do valor
			$prefix = substr($val, 0, strlen(Lumine::DEFAULT_VALUE_FUNCTION_IDENTIFIER));
			// se for para ser aplicado como uma funcao do banco
			if( $prefix == Lumine::DEFAULT_VALUE_FUNCTION_IDENTIFIER ){
				// removemos o prefixo e devolvemos o valor que sera usado como funcao
				return str_replace($prefix, '', $val);
			}
		}
		
		switch($type)
		{
			case 'smallint':
			case 'int':
			case 'integer':
				$val = sprintf('%d', $val);
				break;
		
			case 'float':
			case 'double':
				$val = sprintf('%f', $val);
				break;
			
			case 'date':
				/*
				if(is_numeric($val))
				{
					$val = "'" . date('Y-m-d', $val) . "'";
				} else {
					$val = "'" . date('Y-m-d', strtotime($val)) . "'";
				}*/
				$val = "'" . Lumine_Util::FormatDate( $val, '%Y-%m-%d' ) . "'";
				break;
			
			case 'datetime':
				/*
				if(is_numeric($val))
				{
					$val = "'" . date('Y-m-d H:i:s', $val) . "'";
				} else {
					$val = "'" . date('Y-m-d H:i:s', strtotime($val)) . "'";
				}
				*/
				$val = "'" . Lumine_Util::FormatDateTime( $val, '%Y-%m-%d %H:%M:%S' ) . "'";
				break;
				
			case 'time':
			    $val = Lumine_Util::FormatTime($val, '%H:%M:%S');
			    $val = "'" . $val . "'";
			    break;

			case 'boolean':
				$val = sprintf("'%d'", $val);
				break;
			
			case 'string':
			case 'text':
			case 'varchar':
			case 'char':
			default:
				if( $islike == true)
				{
					$val = "'%" . $obj->_getConnection()->escape( $val ) . "%'";
				} else {
					$val = "'" . $obj->_getConnection()->escape( $val ) . "'";
				}
				break;
		}
		
		return $val;
	}
	
	/**
	 * Faz o parse de valores para SQL
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @param string $str
	 * @return mixed
	 */
	public static function parseSQLValues(Lumine_Base $obj, $str)
	{
		$total = strlen($str);
		$inStr = false;
		$start = '';
		$nova = '';
		$tmp = '';
		
		for($i=0; $i<$total; $i++) 
		{
			$c = substr($str, $i, 1);
			
			if($inStr == false && ($c == "'" || $c == '"'))
			{
				$tmp = self::parseEntityNames($obj, $tmp);
				$nova .= $tmp;
				$tmp = '';
				$inStr = true;
				$start = $c;
				continue;
			}
	
/*			if($inStr == true && $c == $start && substr($str, $i-1, 1) != '\\' && $c != '\\')
			{
				$nova .= $start . $tmp . $start;
				$inStr = false;
				$tmp = '';
				$start = '';
				continue;
				*/
			
			if($inStr == true && $c == $start)
			{
				
				$tmp_test = str_replace( $obj->_getConnection()->getEscapeChar() . $start, '', $c . $tmp . $c);
				
				//if( !substr_count($tmp_test, "'") & 1 )
				if( substr_count($tmp_test, "'") % 2 == 0 )
				{
					$nova .= $start . $tmp . $start;
					$inStr = false;
					$tmp = '';
					$start = '';
					continue;
				}
			}
			
			$tmp .= $c;
		}
		
		if($tmp != '')
		{
			if($inStr == true)
			{
				$tmp = $start . $tmp;
			}
			$nova .= self::parseEntityNames($obj, $tmp);
		}
		
		return $nova;
	}
	
	/**
	 * Faz o parse do objetos em from
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return string
	 */
	public static function parseFromValue( Lumine_Base $obj )
	{
		$schema = $obj->_getConfiguration()->getOption('schema_name');
		if( empty($schema) )
		{
			$name = $obj->metadata()->getTablename();
		} else {
			$name = $schema .'.'. $obj->metadata()->getTablename();
		}
		
		if($obj->alias() != '') 
		{
			$name .= ' '.$obj->alias();
		}
		return $name;
	}
	
	/**
	 * Faz o parse do joins 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @param array $list
	 * @return string
	 */
	public static function parseJoinValues(Lumine_Base $obj, $list)
	{
		$joinStr = implode("\r\n", $obj->_getObjectPart('_join'));
		
		preg_match_all('@(\{(\w+)\.(\w+)\})@', $joinStr, $reg);
		$total = count($reg[0]);
		
		$schema = $obj->_getConfiguration()->getOption('schema_name');
		if( !empty($schema)) 
		{
			$schema .= '.';
		}
		
		for($i=0; $i<$total; $i++)
		{
			if( !empty($reg[2][$i]) && !empty($reg[3][$i])) // exemplo: {Usuario.idusuario}
			{
				// alterado em 28/08/2007
				foreach($list as $ent)
				{
					if($ent->metadata()->getClassname() == $reg[2][$i])
					{
						// $ent = $list[ $reg[2][$i] ];
						$field = $ent->metadata()->getField( $reg[3][$i] );
						$name = $ent->metadata()->getTablename();
						$a = $ent->alias();
						
						if( !empty($a) )
						{
							$name = $a;
						}
						
						$joinStr = str_replace($reg[0][$i], $name . '.' .$field['column'], $joinStr);
					}
				}
				
				/*
				if( !empty( $list[ $reg[2][$i] ]))
				{
					$ent = $list[ $reg[2][$i] ];
					$field = $ent->metadata()->getField( $reg[3][$i] );
					$name = $ent->metadata()->getTablename();
					$a = $ent->alias();
					
					if( !empty($a) )
					{
						$name = $a;
					}
					
					$joinStr = str_replace($reg[0][$i], $name . '.' .$field['column'], $joinStr);
				}
				*/
			}
		}
		
		preg_match_all('@JOIN (\{(\w+)\})@i', $joinStr, $reg);
		$total = count($reg[0]);
		
		for($i=0; $i<$total; $i++)
		{
			if( !empty($reg[2][$i])) // exemplo: (INNER|LEFT|RIGHT) JOIN {Grupo}
			{
				reset($list);
				
				foreach($list as $ent)
				{
					if($ent->metadata()->getClassname() == $reg[2][$i])
					{
						break;
					}
				}
				// $ent = $list[ $reg[2][$i] ];
				$joinStr = str_replace($reg[0][$i], 'JOIN '. $schema . $ent->metadata()->getTablename() .' ' . $ent->alias(), $joinStr);
			}
		}
		
		return "\r\n".$joinStr;
	}
	
	/**
	 * Faz o parse de nomes de colunas e tabelas de uma string
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @param string $str
	 * @return string
	 */
	public static function parseEntityNames(Lumine_Base $obj, $str)
	{
		
		// fazer parse de u.nome (alias + . + nome_do_campo) de cada entidade
		$list = $obj->_getObjectPart('_join_list');
		
		foreach($list as $ent)
		{
			$a = $ent->alias();
			$name = $ent->metadata()->getClassname();
			
			if( !empty($a))
			{
				preg_match_all('@\b'.$a.'\b\.(\w+)\b@', $str, $reg);
				$total = count($reg[0]);
				
				for($i=0; $i<$total; $i++) 
				{
					$field = $ent->metadata()->getField( $reg[1][$i] );
					$exp = '@\b'.$a.'\b\.('.$reg[1][$i].')\b@';
					$str = preg_replace($exp, $a . '.' . $field['column'], $str);
				}
			}
			
			preg_match_all('@\{'.$name.'\.(\w+)\}@', $str, $reg);
			$total = count($reg[0]);
			
			for($i=0; $i<$total; $i++) 
			{
				$field = $ent->metadata()->getField( $reg[1][$i] );
				
				if( !empty($a))
				{
					$str = str_replace($reg[0][$i], $a . '.' . $field['column'], $str);
				} else {
					$str = str_replace($reg[0][$i], $ent->metadata()->getTablename() . '.' . $field['column'], $str);
				}
			}
		}
		
		
		// encontra por {propriedade}
		// quando nao especificado, significa que pertence a mesma entidade
		// chamadora da funcao, por isso nao fazemos loop
		
		preg_match_all('@\{(\w+)\}@', $str, $reg);
		$total = count($reg[0]);
		
		for($i=0; $i<$total; $i++)
		{
			$f = $obj->metadata()->getField($reg[1][$i]);
			$a = $obj->alias();
			
			if($a == '')
			{
				$a = $obj->metadata()->getTablename();
			}
			
			$str = str_replace($reg[0][$i], $a . '.'. $f['column'], $str);
		}
		
		return $str;
	}
	
	/**
	 * Trunca os valores de string conforme o comprimento do campo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param array $prop Propriedades do campo
	 * @param string $value String a ser truncada
	 * @return stirng String truncada
	 */
	public static function truncateValue($prop, $value){
		if( !isset($prop['length']) ){
			return $value;
		}
		
		switch( strtolower($prop['type']) ) {
			case 'text':
			case 'longtext':
			case 'tinytext':
			case 'blob':
			case 'longblob':
			case 'tinyblob':
			case 'varchar':
			case 'varbinary':
			case 'char':
				if( strlen($value) > $prop['length'] ){
					Lumine_Log::warning('Truncando valor do campo ' . (isset($prop['name']) ? $prop['name'] : $prop['column']) . ' ('.$prop['length'].')' );
					$value = substr($value, 0, $prop['length']);
				}
			break;
		}
		
		return $value;
	}
}


?>