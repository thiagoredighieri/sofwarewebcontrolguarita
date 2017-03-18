<?php
/**
 * Classe com funcoes utilitarias
 *
 * @package Lumine_Util
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

/**
 * Classe com funcoes utilitarias
 *
 * @package Lumine_Util
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Util
{
	/**
	 * Importa classes
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	public static function import()
	{
		$list = func_get_args();
		foreach($list as $item)
		{
			$parts = explode(".", $item);
			$class = array_pop($parts);

			$cm = Lumine_ConnectionManager::getInstance();
			$cfg = $cm->getConfiguration( implode('.', $parts) );
			if($cfg != false)
			{
				$cfg->import($class);
			}
		}
	}

	/**
	 * Funcao para formatar uma data de acordo com o formato desejado
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $date   Data de entrada
	 * @param string $format Formato desejado de saida
	 * @return string
	 */
	public static function FormatDate($date, $format = "%d/%m/%Y") {
		$v = $date;
		if(is_numeric($date)) {
			return strftime($format, $date);
		}
		$formats = array("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/",
						"/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/");
		//$replaces = array(
		if(preg_match($formats[0], $date, $d)) {
			$v = $date;
		}
		if(preg_match($formats[1], $date, $d)) {
			if(checkdate($d[2], $d[1], $d[3])) {
				$v = "$d[3]-$d[2]-$d[1]";
			} else {
				$v = "$d[3]-$d[1]-$d[2]";
			}
		}
		$s = strtotime($v);
		if($s > -1) {
			return strftime($format, $s);
		}
		return $v;
	}

	/**
	 * Funcao para formatar um horario de acordo com o formato desejado
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $time   Horario de entrada
	 * @param string $format Formato desejado de saida
	 * @return string
	 */
	public static function FormatTime($time, $format = "%H:%M:%S") {
		if(is_numeric($time)) {
			return strftime($format, $time);
		}
		$v = $time;
		$t = strtotime($v);
		if($t > -1) {
			$v = strftime($format, $t);
		}
		return $v;
	}

	/**
	 * Formata uma data e horario conforme o desejado
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $time Data/hora de entrada
	 * @param string $format Formato desejado
	 * @return string data e hora formatadas
	 * @return string
	 */
	public static function FormatDateTime($time, $format = "%Y-%m-%d %H:%M:%S") {
		if(is_numeric($time)) {
			return strftime($format, $time);
		}
		// 2005-10-15 12:29:32
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $time, $reg)) {
			return strftime($format, strtotime($time));
		}
		// 2005-10-15 12:29
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2})$/", $time, $reg)) {
			return strftime($format, strtotime($time));
		}
		// 2005-10-15 12
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})$/", $time, $reg)) {
			return strftime($format, strtotime($time));
		}
		// 2005-10-15
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $time, $reg)) {
			return self::FormatDate($time, $format);
		}
		// 15/10/2005 12:29:32
		if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $time, $reg)) {
			$isodate = self::FormatDate("$reg[1]/$reg[2]/$reg[3]", "%Y-%m-%d");
			return strftime($format, strtotime("$isodate $reg[4]:$reg[5]:$reg[6]"));
		}
		// 15/10/2005 12:29
		if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2})$/", $time, $reg)) {
			$isodate = self::FormatDate("$reg[1]/$reg[2]/$reg[3]", "%Y-%m-%d");
			return strftime($format, strtotime("$isodate $reg[4]:$reg[5]:00"));
		}
		// 15/10/2005 12
		if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2})$/", $time, $reg)) {
			$isodate = self::FormatDate("$reg[1]/$reg[2]/$reg[3]", "%Y-%m-%d");
			return strftime($format, strtotime("$isodate $reg[4]"));

		}
		// 15/10/2005
		if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/", $time, $reg)) {
			return self::FormatDate($time, $format);
		}
		return $time;
	}

	/**
	 * Cria diretorios recursivamente
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $dir  diretorio a ser criado
	 * @param string|boolean $dono dono do diretorio ou false para nao criar
	 * @return boolean
	 */
	public static function mkdir($dir, $dono = false) {
		if(file_exists($dir) && is_dir($dir)) {
			return true;
		}
		$dir = str_replace("\\","/", $dir);
		$pieces = explode("/", $dir);

		for($i=0; $i<count($pieces); $i++) {
			$mdir = '';
			for($j=0; $j<=$i; $j++) {
				$mdir .= $pieces[$j] != '' ? $pieces[$j] . "/" : '';
			}
			$mdir = substr($mdir, 0, strlen($mdir)-1);
			if(!file_exists($mdir) && $mdir != '') {
				mkdir($mdir, 0777) or die("Falha ao criar o diret�rio <strong>$mdir</strong>");
				@chmod($mdir, 0777);
				if($dono !== false) {
					chown($mdir, $dono);
				}
			}
		}
		return true;
	}

	/**
	 * Valida um email
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $email E-mail para validacao
	 * @return boolean
	 */
	public static function validateEmail ($email ) {
		if($email == '') {
			return false;
		}
		return (boolean)preg_match("#^([0-9,a-z,A-Z]+)([.,_,-]*([0-9,a-z,A-Z]*))*[@]([0-9,a-z,A-Z]+)([.,_,-]([0-9,a-z,A-Z]+))*[.]([0-9,a-z,A-Z]){2}([0-9,a-z,A-Z])?$#", $email);
	}

	/**
	 * Monta uma lista de options para ser usada em um <select> HTML
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base|string $class Nome da classe qualificado ou instancia de uma Lumine_Base
	 * @param string $value Nome do campo que contem os valores reais
	 * @param string $label Nome do campo que servira como label
	 * @param string $selected Valor que sera selecionado por padrao
	 * @param string $where Parametros adicionais para uma consulta
	 * @return string Elementos gerados em HTML
	 */
	public static function buildOptions($class, $value, $label, $selected='', $where=null) {
		if(is_string($class)) {
			self::Import($class);

			$classname = substr($class, strrpos($class,'.')+1);

			$o = new $classname;
			$o->alias('o');

			if($o) {
				if( !empty($where)) {
					$o->where($where);
				}
				$o->select("o.$value, o.$label");
				$o->order("o.$label asc");
				$o->find();
			}
		} else if($class instanceof Lumine_Base) {
			$o = &$class;
		} else {
			return false;
		}

		$str='';
		while($o->fetch()) {
			$str .= '<option value="'.$o->$value.'"';
			if($o->$value == $selected) {
				$str .= ' selected="selected"';
			}
			$str .= '>'.$o->$label.'</option>' . PHP_EOL;
		}
		return $str;
	}

	/**
	 * Converte os valores para UTF-8
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param mixed $o Dado a ser convertido
	 * @return mixed Dados convertidos
	 */
	public static function toUTF8( $o ) {
		if(is_string($o)) {
			//$o = preg_replace('/([^\x09\x0A\x0D\x20-\x7F]|[\x21-\x2F]|[\x3A-\x40]|[\x5B-\x60])/e', '"&#".ord("$0").";"', $o);
			$o = utf8_encode($o);
			//$o = preg_replace('@&([a-z,A-Z,0-9]+);@e','html_entity_decode("&\\1;")',$o);
			return $o;
		}
		if(is_array($o)) {
			foreach($o as $k=>$v) {
				$o[$k] = self::toUTF8($o[$k]);
			}
			return $o;
		}
		if(is_object($o)) {
			$l = get_object_vars($o);
			foreach($l as $k=>$v) {
				$o->$k = self::toUTF8( $v );
			}
		}
		// padrao
		return $o;
	}

	/**
	 * Converte os valores de UTF-8
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param mixed $o Dado a ser convertido
	 * @return mixed Dados convertidos
	 */
	public static function fromUTF8( $o ) {
		if(is_string($o)) {
			//$o = preg_replace('/([^\x09\x0A\x0D\x20-\x7F]|[\x21-\x2F]|[\x3A-\x40]|[\x5B-\x60])/e', '"&#".ord("$0").";"', $o);
			$o = utf8_decode($o);
			//$o = preg_replace('@&([a-z,A-Z,0-9]+);@e','html_entity_decode("&\\1;")',$o);
			return $o;
		}
		if(is_array($o)) {
			foreach($o as $k=>$v) {
				$o[$k] = self::fromUTF8($o[$k]);
			}
			return $o;
		}
		if(is_object($o)) {
			$l = get_object_vars($o);
			foreach($l as $k=>$v) {
				$o->$k = self::fromUTF8( $v );
			}
		}
		// padrao
		return $o;
	}

	/**
	 * Exibe os resultados de uma consulta em uma tabela HTML
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return void
	 */
	public static function showResult(Lumine_Base $obj)
	{
		$sql = $obj->_getSQL();
		$resultset = $obj->allToArray();

		if( !empty($resultset) )
		{
			$header = $resultset[0];

			$style = ' style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:9px" ';

			echo '<table cellpadding="2" cellspacing="1" width="100%">';
			echo '<tr>';

			echo '<tr>'.PHP_EOL;
			echo '<td '.$style.' colspan="'.count($header).'">' . $sql. '</td>'.PHP_EOL;
			echo '</tr>' . PHP_EOL;

			foreach($header as $key => $value)
			{
				echo '<td'.$style.' bgcolor="#CCCCCC">'. $key .'</td>'.PHP_EOL;
			}
			echo '</tr>';

			for($i=0; $i<count($resultset); $i++)
			{
				$row = $resultset[$i];
				$cor = $i%2!=0?'#EFEFEF':'#FFFFFF';
				echo '<tr>';
				foreach($row as $value)
				{
					echo '<td'.$style.' bgcolor="'.$cor.'">'.$value.'</td>'.PHP_EOL;
				}
				echo '</tr>';
			}

			echo '</table>';
		} else {
			Lumine_Log::warning( 'Nenhum resultado encontrado no objeto passado: ' . get_class($obj) );
		}
	}

	/**
	 * Converte um array para xml
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $arr
	 * @param boolean $utf8
	 * @param boolean $first
	 * @return string
	 */
	public static function array2xml( $arr, $utf8 = true, $first = true, $useAttributes = false, $nodeName = '' )
	{
	    $xml = array();
	    $stack = array();


	    if( !$useAttributes ){
		    foreach( $arr as $key => $item )
		    {
		        if( is_numeric($key) )
		        {
		            $key = 'item';
		        }
	
		        $xml[] = sprintf('<%s>', $key);
	
		        if( is_array($item) ) {
	                $str = self::array2xml($item, $utf8, false, $useAttributes);
		        } else {
		            $str = sprintf('<![CDATA[%s]]>', $utf8 ? utf8_encode($item) : $item);
		        }
	
		        $xml[] = $str;
		        $xml[] = sprintf('</%s>', $key);
		    }
	    } else {
	    	
	    	$lists = array();
	    	$nodeName = empty($nodeName) ? 'record' : $nodeName;
	    	$line = $first ? '' : '<' . $nodeName . ' ';
	    	
			foreach( $arr as $key => $item )
		    {
		        if( is_numeric($key) )
		        {
		            $key = 'item';
		        }
	
		        if( is_array($item) ) {
		        	$lists[] = array('data' => $item, 'key' => $key);
		        } else {
		            $line .= sprintf('%s="%s" '
		            	, $key
		            	, $utf8 ? utf8_encode($item) : $item
		            );
		        }
		    }
		    
		    $line .= $first ? '' : '>';
		    
		    foreach($lists as $item){
		    	$line .= self::array2xml($item['data'], $utf8, false, $useAttributes, $item['key']);
		    }
		    
		    $line .= $first ? '' : '</' . $nodeName . '>';
		    $xml[] = $line;
	    }

	    if( $first ) {
	        return '<data>' . implode(PHP_EOL, $xml) . '</data>';
	    } else {
	        return implode(PHP_EOL, $xml);
	    }
	}

	/**
	 * Converte valores para o formato JSON
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/lumine
	 * @param mixed $value valor a ser convertido
	 * @param boolean $utf8 indica se o valor devera ser convertido em utf8 antes de ser transformado em json
	 * @return string Valor na representacao JSON
	 */
	public static function json($value, $utf8 = false){
		if($utf8){
			$value = self::toUTF8($value);
		}

		if(!function_exists('json_encode')){
			throw new Exception('O metodo json_encode nao existe');
		}

		return json_encode($value);
	}

	/**
	 * Coloca a string no formato camel case
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param unknown_type $field
	 * @return
	 */
	public static function camelCase($field){
		return preg_replace('@_(\w{1})@e', 'strtoupper("$1")', strtolower($field) );
	}
}



?>