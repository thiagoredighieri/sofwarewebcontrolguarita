<?php
/**
 * Cliente de atualizacao.
 * 
 * A ideia e que futuramente a atualizacao de Lumine seja feita de forma mais facil
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */


/**
 * Cliente de atualizacao.
 * 
 * A ideia e que futuramente a atualizacao de Lumine seja feita de forma mais facil
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */
class Lumine_Updater extends Lumine_EventListener
{
	/**
	 * lista de arquivos
	 * @var array
	 */
	private $filelist = array();
	
	/**
	 * Recupera uma lista de arquivos
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return array 
	 */
	public function getFileList()
	{
		$this->filelist = array();
		$this->_getFilesFromDir( LUMINE_INCLUDE_PATH );
		
		return $this->filelist;
	}
	
	/**
	 * Recupera os arquivos de um diretorio
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $dir
	 * @return array
	 */
	private function _getFilesFromDir( $dir )
	{
		$dh = opendir( $dir );
		while( ($file = readdir($dh)) !== false )
		{
			if($file == '.' || $file == '..')
			{
				continue;
			}
			
			if(is_dir($dir .'/'. $file))
			{
				$this->_getFilesFromDir( $dir . '/' . $file );
			} else {
				$ds = DIRECTORY_SEPARATOR;
				$filename = $dir . $ds . $file;
				
				$item = array(
					'filename' => str_replace(LUMINE_INCLUDE_PATH . $ds, '', $filename),
					'filesize' => filesize($filename),
					'filedate' => filemtime($filename)
				);
				$this->filelist[] =  $item;
			}
		}
		
		closedir($dh);
	}

}


?>