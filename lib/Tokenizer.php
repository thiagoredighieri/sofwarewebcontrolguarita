<?php
/**
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

/**
 * Classe para transformar strings em tokens
 * 
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Tokenizer 
{
	/**
	 * Faz o parse do dataselect de uma instancia
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $dataStr
	 * @param Lumine_Base $obj
	 * @return string
	 */
	public static function dataSelect( $dataStr, Lumine_Base $obj )
	{
		$idx = 0;
		$total = strlen($dataStr);
		$d =',';
		$tokens = array();

		$inStr = false;
		$inFunction = 0;
		$inStrStart = '';
		
		for($i=0; $i<$total; $i++)
		{
			$c = substr($dataStr, $i, 1);
			
			if($c == '(' && ! $inStr) {
				$inFunction++;
			}
			
			if($c == ')' && ! $inStr) {
				$inFunction--;
			}
			
			if( ! $inStr && ($c == '"' || $c == "'") && substr($dataStr, $i-1, 1) != '\\' && $c != '\\') {
				$inStr = true;
				$inStrStart = $c;
			}
			
			if($inStr == true && $c == $inStrStart)
			{
				if(!empty($tokens)){
					$tmp_test = str_replace( $obj->_getConnection()->getEscapeChar() . $inStrStart, '', $c . $tokens[$idx] . $c);
					if( substr_count($tmp_test, "'") % 2 == 0 ) {
						$inStr = false;
						$tmp = '';
						$inStrStart = '';
					}
				}
			} 

			if( $inFunction == 0 && ! $inStr && $c == $d)
			{
				$idx++;
				continue;
			}
			
			if(!isset($tokens[$idx])) 
			{
				$tokens[$idx] = '';
			}
			$tokens[$idx] .= $c;
		}
		
		foreach($tokens as $id => $token)
		{
			$tokens[ $id ] = trim($token);
		}
		
		return $tokens;
	}
	
	/**
	 * Faz o parse da clasula where
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $str
	 * @return void
	 * @deprecated
	 */
	public function where( $str )
	{
		
	}
	
}




?>