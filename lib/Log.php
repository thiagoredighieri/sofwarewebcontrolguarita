<?php
/**
 * Classe para poder visualizar o que lumine esta fazendo.
 * 
 * A partir dele e possivel acessar varios niveis de log 
 * 
 * <code>
 * Lumine_Log::setLevel(1); // nivel mais baixo
 * Lumine_Log::setLevel(2); // nivel medio
 * Lumine_Log::setLevel(3); // nivel mais alto
 * <code>
 * 
 * Ainda e possivel enviar a saida para um arquivo
 * <code>
 * Lumine_Log::setOutput(Lumine_Log::FILE, 'log.txt');
 * </code>
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

/**
 * Classe para exibir logs
 * 
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Log {
	
	/**
	 * nao exibir log
	 * @var int
	 */
	const NONE                   = 0;
	/**
	 * nivel mais baixo de log
	 * @var int
	 */
	const LOG                    = 1;
	/**
	 * nivel medio log
	 * @var int
	 */
	const WARNING                = 2;
	/**
	 * nivel mais alto de erro
	 * @var int
	 */
	const ERROR                  = 3;
	/**
	 * saida no browser
	 * @var string
	 */
	const BROWSER                = 'browser';
	/**
	 * saida em arquivo
	 * @var string
	 */
	const FILE                   = 'file';

	/**
	 * cor para quando for debug normal
	 * @var string
	 */
	public static $LOG_COLOR     = '#000000';
	/**
	 * cor para quando for warning
	 * @var string 
	 */
	public static $WARNING_COLOR = '#FF9900';
	/**
	 * cor para quando for erro
	 * @var string
	 */
	public static $ERROR_COLOR   = '#FF0000';
	/**
	 * nivel de log
	 * @var string
	 */
	public static $level         = 0;
	/**
	 * tipo de saida do log
	 * @var string
	 */
	public static $output        = self::BROWSER;
	/**
	 * nome do arquivo de saida do log
	 * @var string
	 */
	public static $filename      = '';
	
	/**
	 * timezone padrao
	 * @var string
	 */
	private static $timezone     = 'America/Sao_Paulo';
	
	/**
	 * Efetua o log 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param int    $code  nivel de erro
	 * @param string $msg   mensagem de log
	 * @param string $file  nome do arquivo 
	 * @param int    $line  numero da linha
	 * @return void
	 */
	public static function log($code, $msg, $file, $line)
	{
		if( self::$level < $code ){
			return;
		}
		
		$tipo = 'DESCONHECIDO';
		$cor = '';

		switch($code)
		{
			case self::LOG:
				$tipo = 'LOG';
				$cor = self::$LOG_COLOR;
				break;
				
			case self::WARNING:
				$tipo = 'ALERTA';
				$cor = self::$WARNING_COLOR;
				break;
				
			case self::ERROR:
				$tipo = 'ERRO';
				$cor = self::$ERROR_COLOR;
				break;
		}
		
		if(self::$level >= $code)
		{
//			if( function_exists('date_default_timezone_set')) {
//				date_default_timezone_set(self::$timezone);
//			}
			
			$data = date('d/m/Y H:i:s');
			$msg = "<pre style=\"color:$cor\"><strong>$data - $tipo: </strong> $msg ($file, $line)</pre>".PHP_EOL;
			switch(self::$output)
			{
				case self::BROWSER:
					echo $msg;
					break;
				
				case self::FILE:
					$msg = strip_tags($msg);
					
					if( ! empty(self::$filename))
					{
						$fp = @fopen(self::$filename, 'a+');
						if( $fp )
						{
							fwrite($fp, $msg);
							fclose($fp);
						}
					}
			}
		}
	}
	
	public static function setLevel( $newLevel = self::NONE ) 
	{
		self::$level = $newLevel;
	}
	
	/**
	 * Altera o tipo de saida
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param int     $type      tipo de saida dos dados
	 * @param string  $filename  nome do arquivo caso a saida seja para um arquivo
	 * @return void
	 */
	public static function setOutput($type = self::BROWSER, $filename = null)
	{
		self::$output = $type;
		self::$filename = $filename;
	}
	
	/**
	 * Dispara um log de nivel medio (WARNING)
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $message mensagem
	 * @return void
	 */
	public static function warning($message)
	{
		if( self::$level < self::WARNING ){
			return;
		}
		$tmp = debug_backtrace();
		$bt = array_shift( $tmp );
		$file = $bt['file'];
		$line = $bt['line'];
		self::log(self::WARNING, $message, $file, $line);
	}
	
	/**
	 * Dispara um log de nivel baixo (DEBUG)
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $message mensagem
	 * @return void
	 */
	public static function debug($message)
	{
		if( self::$level < self::LOG ){
			return;
		}
		$tmp = debug_backtrace();
		$bt = array_shift( $tmp );
		$file = $bt['file'];
		$line = $bt['line'];
		self::log(self::LOG, $message, $file, $line);
	}
	
	/**
	 * Dispara um log de nivel critico (ERROR)
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $message mensagem
	 * @return void
	 */
	public static function error($message)
	{
		if( self::$level < self::ERROR ){
			return;
		}
		$tmp = debug_backtrace();
		$bt = array_shift( $tmp );
		$file = $bt['file'];
		$line = $bt['line'];
		self::log(self::ERROR, $message, $file, $line);
	}
}


?>