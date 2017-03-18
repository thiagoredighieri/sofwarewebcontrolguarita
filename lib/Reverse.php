<?php
/**
 * Classe para efetuar a engenharia reversa
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @license http://www.gnu.org/licenses/lgpl.html LPGL
 * @package Lumine
 */

Lumine::load('Reverse_ClassTemplate','Reverse_ConfigurationTemplate','Reverse_DTOTemplate','Reverse_ModelTemplate');
Lumine::load('Lumine_Utils_dZip.inc');

/**
 * Classe para efetuar a engenharia reversa
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @license http://www.gnu.org/licenses/lgpl.html LPGL
 * @package Lumine
 */
class Lumine_Reverse extends Lumine_EventListener
{

	/**
	 * tabelas que serviram como many-to-many
	 * @var array
	 */
	private $many_to_many         = array();
	/**
	 * nomes das tabelas a serem analisadas
	 * @var array
	 */
	private $tables               = array();
	
	/**
	 * Configuracao
	 * @var Lumine_Configuration
	 */
	private $cfg                  = null;
	/**
	 * classes a serem geradas
	 * @var unknown_type
	 */
	private $classes              = array();
	/**
	 * classes de DTO a serem geradas
	 * @var array
	 */
	private $dtos                 = array();
	/**
	 * arquivos a serem gerados
	 * @var array
	 */
	private $files                = array();
	/**
	 * arquivos a serem gerados DTO
	 * @var array
	 */
	private $dto_files            = array();
	/**
	 * arquivos a serem gerados para Models
	 * @var array
	 */
	private $model_files          = array();
	/**
	 * classes das models
	 * @var array
	 */
	private $models               = array();
	/**
	 * controles a serem criados
	 * @var array
	 */
	private $controls             = array();
	/**
	 * configuracoes
	 * @var string
	 */
	private $config               = '';
	/**
	 * opcoes originais
	 * @var array
	 */
	private $original_options     = array();
	/**
	 * Dialeto que sera usado
	 * @var Lumine_Dialect_IDialect
	 */
	private $dialect              = null;
	
	/**
	 * Construtor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $options As mesmas opcoes do arquivo de configuracao
	 * @return Lumine_Reverse
	 */
	public function __construct(array $options)
	{
		$this->cfg = new Lumine_Configuration( $options );
		$this->original_options = $options;
	}

	/**
	 * Inicia o processo da engenharia reversa
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	public function start()
	{
		Lumine_Log::debug('Iniciando engenharia reversa');
		$cfg = $this->cfg;
		$dbh = $cfg->getConnection();
		$dbh->connect();
		
		$dialect = $cfg->getProperty('dialect');
		$class_dialect = 'Lumine_Dialect_'.$cfg->getProperty('dialect');
		Lumine::load($class_dialect);
		
		$this->dialect = new $class_dialect(null);

		if(empty($this->tables))
		{
			$this->tables = $dbh->getTables();
		}

		$many_to_many = array();
		$mtm_style = $cfg->getOption('many_to_many_style');
		if( empty($mtm_style)) {
			$mtm_style = '%s_%s';
		}
		
		for($i=0; $i<count($this->tables); $i++) {
			for($j=0; $j<count($this->tables); $j++) {
				$rel = sprintf($mtm_style, $this->tables[$i], $this->tables[$j]);

				if(in_array($rel, $this->tables)) {
					if(!array_key_exists($rel, $many_to_many)) {
						$many_to_many[] = $rel;
					}
					continue;
				}
				
			}
		}
		
		$camel = $cfg->getOption('camel_case');
		foreach($this->tables as $table)
		{
			if(in_array($table, $many_to_many) && $cfg->getOption('create_entities_for_many_to_many') != true)
			{
				continue;
			}
			
			Lumine_Log::debug('Analisando tabela '.$table);
			$classname = $table;
			if( $cfg->getOption('remove_prefix'))
			{
				Lumine_Log::debug('Removendo prefixo da tabela '.$table);
				$classname = preg_replace('@^'.$cfg->getOption('remove_prefix').'@', '', $classname);
			}
			if( $cfg->getOption('remove_count_chars_start') > 0)
			{
				Lumine_Log::debug('Removendo os primeiros '.$cfg->getOption('remove_count_chars_start') . ' caracteres de '.$table);
				$classname = substr($classname, $cfg->getOption('remove_count_chars_start'));
			}
			if( $cfg->getOption('remove_count_chars_end'))
			{
				Lumine_Log::debug('Removendo os ultimos '.$cfg->getOption('remove_count_chars_start') . ' caracteres de '.$table);
				$classname = substr($classname, 0, strlen($classname) - $cfg->getOption('remove_count_chars_end'));
			}
			
			$classname = ucfirst(strtolower($classname));
			
			if( $cfg->getOption('format_classname') != ''){
				Lumine_Log::debug('Formatando o nome da classe de '.$table);
				$classname = sprintf($cfg->getOption('format_classname'), $classname);
			}
			
			$field_list = $dbh->describe( $table );

			
			Lumine_Log::debug('Criando entidade reversa de '.$table);
			$formatter = 'Lumine_Templates_'.ucfirst($cfg->getOption('classMapping')).'Template';
			$obj = new Lumine_Reverse_ClassTemplate($table, $classname, $cfg->getProperty('package'));
			$obj->setFormatter(new $formatter());
			
			
			Lumine_Log::debug('Recuperando chaves estrangeiras de '.$table);
			$obj->setForeignKeys( $dbh->getForeignKeys( $table ) );
			
			Lumine_Log::debug('Recuperando os campos de '.$table);
			$obj->setDescription( $field_list );
			
			$obj->setDialect( $this->dialect );
			
			$obj->setCamelCase( ! empty($camel) );
			
			$obj->setGenerateAccessors($cfg->getOption('generateAccessors'));
			
			// dto para flex 
			$dto = new Lumine_Reverse_DTOTemplate($classname, $field_list, $this->cfg->getOption('dto_format'));
			$dto->setCamelCase( ! empty($camel) );
			
			// 2011-04-15
			// dto's podem ter pacotes diferentes
			$mapping = $this->cfg->getOption('dto_package_mapping');
			if( empty($mapping[$table]) ){
				$dto->setPackage($this->cfg->getOption('dto_package'));
			} else {
				$dto->setPackage($mapping[$table]);
			}
						
			$this->classes[ $table ] = $obj;
			$this->dtos[ $table ] = $dto;
			
			if($cfg->getOption('create_models') == 1){
				$model = new Lumine_Reverse_ModelTemplate($obj->getClassname(), $cfg->getOption('model_format'));
				$model->setModelsPath($cfg->getProperty('class_path') . '/' . $cfg->getOption('model_path'));
				$this->models[ $table ] = $model;
			}
		}

		$this->many_to_many = $many_to_many;
		unset($many_to_many);

		$this->checkRerentialIntegrity();
		$this->createFiles();

		$controls  = $cfg->getOption('create_controls');		
		$to_zip    = $cfg->getOption('generate_zip');
		$to_files  = $cfg->getOption('generate_files');
		$overwrite = $cfg->getOption('overwrite');
		
		$this->createConfigurationsFile();
		
		if( !empty($controls))
		{
			$this->createControls( $controls );
		}
		
		if( !empty($to_zip))
		{
			$this->generateZip();
		}
		if( !empty($to_files))
		{
			$this->generateFiles($overwrite);
		}
	}
	
	/**
	 * Seta a lista de tabelas 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $list
	 * @return void
	 */
	public function setTables( array $list)
	{
		$this->tables = $list;
	}
	
	/**
	 * Checa a integridade referencia entre as tabelas
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	private function checkRerentialIntegrity()
	{
		$cfg = $this->cfg;
		$dbh = $cfg->getConnection();
		
		// gera as referencias de cada classe
		foreach($this->classes as $tablename => $obj)
		{
			$fks = $obj->getForeignKeys();
			foreach($fks as $from => $def)
			{
				$defx = $obj->getDefColumn( $def['from'] );
				
				if( empty($this->classes[$def['to']]))
				{
					Lumine_Log::error('Erro na integridade referencial de '.$tablename .' para '. $def['to']);
					exit;
				}
				
				$colNameTo = $this->cfg->getOption('keep_foreign_column_name') == true ? $def['to_column'] : $this->classes[ $def['to'] ]->getClassname();
				
				if( $this->cfg->getOption('keep_foreign_column_name') == true )
				{				
					$defx[0] = $def['from'];
				} else {
					$defx[0] = strtolower($this->classes[ $def['to'] ]->getClassname());
				}
				
				$defx['options'] = array(
					'column'   => $def['from'],
					'foreign'  => true,
					'onUpdate' => $def['update'],
					'onDelete' => $def['delete'],
					'linkOn'   => $def['to_column'], //$colNameTo,
					'class'    => $this->classes[ $def['to'] ]->getClassname()
				);
				
				$obj->setDefColumn( $def['from'], $defx);
				
				
				$rel = array(
					'class'     => $obj->getClassname(),
					'linkOn'    => $defx[0],
					'name'      => $this->toPlural( $obj->getClassname() )
				);
				
				$this->classes[ $def['to'] ]->addOneToMany( $rel );
				$this->dtos[ $def['to'] ]->addOneToMany( $rel );
			}
		}
		
		// gera as referencias many-to-many
		foreach($this->many_to_many as $mtm)
		{
			$fks = $dbh->getForeignKeys( $mtm );
			$keys = array_keys($fks);

			$fk_1 = $fks[ $keys[0] ];
			$fk_2 = $fks[ $keys[1] ];

			$col_def_1 = $this->classes[ $fk_1['to'] ]->getDefColumn( $fk_1['to_column'] );
			$col_def_2 = $this->classes[ $fk_2['to'] ]->getDefColumn( $fk_2['to_column'] );
			
			$def_1 = array(
				'name'        => $this->toPlural( $this->classes[ $fk_1['to'] ]->getClassname() ),
				'class'       => $this->classes[ $fk_1['to'] ]->getClassname(),
				'linkOn'      => $col_def_2[0],
				'type'        => 'MANY_TO_MANY',
				'table_join'  => $mtm,
				'column_join' => $fk_2['from'],
				'lazy'        => 'null'
			);

			$def_2 = array(
				'name'        => $this->toPlural( $this->classes[ $fk_2['to'] ]->getClassname() ),
				'class'       => $this->classes[ $fk_2['to'] ]->getClassname(),
				'linkOn'      => $col_def_1[0],
				'type'        => 'MANY_TO_MANY',
				'table_join'  => $mtm,
				'column_join' => $fk_1['from'],
				'lazy'        => 'null'
			);

			$this->classes[ $fk_1['to'] ]->addManyToMany( $def_2 );
			$this->classes[ $fk_2['to'] ]->addManyToMany( $def_1 );
			
			$this->dtos[ $fk_1['to'] ]->addManyToMany( $def_2 );
			$this->dtos[ $fk_2['to'] ]->addManyToMany( $def_1 );
		}
	}
	
	/**
	 * Inicia a gravacao dos arquivos
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	private function createFiles()
	{
		reset($this->classes);
		reset($this->dtos);
		
		foreach($this->classes as $table => $obj)
		{
			Lumine_Log::debug('Gerando arquivo para '.$obj->getClassname());
			$this->files[ $obj->getClassname() ] = $obj->getGeneratedFile();
		}
		
	}
	
	/**
	 * Converte palavras do singular para o plural
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name palavra a ser convertida
	 * @return string
	 */
	private function toPlural( $name )
	{
		$pl = $this->cfg->getOption('plural');
        $useDictionary = $this->cfg->getOption('usar_dicionario');

        /**
         * @TODO melhorar o esquema de pluralizacao
         */
        if($useDictionary){
            $finais = array('@al$@','@el$@','@il$@','@ol$@','@ao$@','@ou$@','@or$@','@a$@','@e$@','@i$@','@o$@','@u$@','@er$@');
            $troca = array('ais','eis','is','ois','oes','aram','ores','as','es','is','os','us','eres');
            
            $name = preg_replace($finais,$troca,$name);
            
        } else if( !empty($pl)) {
			$name .= $pl;
		}
		return strtolower($name);
	}
	
	/**
	 * Gera um arquivo ZIP contendo as classes geradas
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	private function generateZip()
	{
		Lumine_Log::debug('Gerando arquivo ZIP');
		$raiz = $this->cfg->getProperty('class_path') . '/';
		$zipname = $this->cfg->getProperty('class_path') .'/lumine.zip';
		
		if( !is_writable($raiz))
		{
			Lumine_Log::error('Nao e possivel criar arquivos em "'.$raiz.'". Verifique as permissoes.');
			exit;
		}
		
		$zip = new dZip($zipname);
		$sufix = $this->cfg->getOption('class_sufix');
		if( !empty($sufix))
		{
			$sufix = '.' .$sufix;
		}
		$filename = str_replace('.', DIRECTORY_SEPARATOR, $this->cfg->getProperty('package'));
		$filename .= DIRECTORY_SEPARATOR;
		
		reset($this->files);
		foreach($this->files as $classname => $content)
		{
			Lumine_Log::debug('Adicionando '.$classname . ' ao ZIP');
			$name = $filename . $classname . $sufix . '.php';
			$zip->addFile($content, $name, 'Lumine Reverse', $content);
		}
		
		// adiciona os dto's
		reset($this->dto_files);
		
		// 2010-11-05
		// permite configurar a pasta para gravar os dto's
		$dtopath = '';
		if( $this->cfg->getOption('dto_package') != '' ){
			$dtopath = str_replace('.','/', $this->cfg->getOption('dto_package'));
		}
		
		foreach($this->dto_files as $classname => $content)
		{
			Lumine_Log::debug('Adicionando DTO '.$classname . ' ao ZIP');
			$name = $filename . 'dto/' . $dtopath . $classname . $sufix . '.php';
			$zip->addFile($content, $name, 'Lumine Reverse DTO', $content);
		}
		
		// models
		$path = $this->cfg->getOption('model_path') . '/';
		
		foreach($this->models as $item){
			Lumine_Log::debug('Adicionando Model '.$classname . ' ao ZIP');
			
			$filename = $path . $item->getFileName();
			$content = $item->getContent();
			$zip->addFile($content, $filename, 'Lumine Reverse Model', $content);
			
		}
		
		// adiciona os controles
		$path = 'controls' . DIRECTORY_SEPARATOR;
		foreach($this->controls as $classname => $content)
		{
			Lumine_Log::debug('Adicionando controle '.$classname . ' ao ZIP');
			$name = $path . $classname . '.php';
			$zip->addFile($content, $name, 'Lumine Reverse Control', $content);
		}
		
		$zip->addFile($this->config, 'lumine-conf.php', 'Configuration File', $this->config);
		$zip->save();
		// altera as permissoes do arquivo
		chmod($zipname, 0777);
		
		Lumine_Log::debug('Arquivo ZIP gerado com sucesso em '.$zipname);
		
		/*
		$fp = @fopen($zipname, "wb+");
		if($fp)
		{
			fwrite($fp, $zip->getZippedfile());
			fclose($fp);
			
			chmod($zipname, 0777);
			
			Lumine_Log::debug('Arquivo ZIP gerado com sucesso em '.$zipname);
		} else {
			Lumine_Log::error('Falha ao gerar ZIP em '.$obj->getClassname().'. Verifique se a pasta existe e se tem direito de escrita.');
			exit;
		}
		*/
		
	}
	
	/**
	 * Gera os arquivos
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param boolean $overwrite Forca a sobrescrita nos arquivos
	 * @return void
	 */
	private function generateFiles( $overwrite )
	{
		Lumine_Log::debug('Gerando arquivos direto na pasta');
		$fullpath = $this->cfg->getProperty('class_path') . DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,$this->cfg->getProperty('package'));
		
		$sufix = $this->cfg->getOption('class_sufix');
		if( !empty($sufix))
		{
			$sufix = '.' . $sufix;
		}

		$dummy = new Lumine_Reverse_ClassTemplate();
		$end   = $dummy->getEndDelim();
		
		if(!file_exists($fullpath) && $this->cfg->getOption('create_paths') == 1){
			mkdir($fullpath,0777,true) OR die('Não foi possivel criar o diretorio: ' . $fullpath);
		}
		
		reset($this->files);
		foreach($this->files as $classname => $content)
		{
			$filename = $fullpath . DIRECTORY_SEPARATOR . $classname . $sufix . '.php';
			
			if(file_exists($filename) && empty($overwrite))
			{
				$fp = fopen($filename, 'r');
				$old_content = fread($fp, filesize($filename));
				fclose($fp);
				
				$start       = strpos($old_content, $end) + strlen($end);
				
				$customized  = substr($old_content, $start);
				$top         = substr($content, 0, strpos($content, $end));
				
				$content     = $top . $end . $customized;
			}
			
			$fp = @fopen($filename, 'w');
			if($fp)
			{
				fwrite($fp, $content);
				fclose($fp);
				chmod($filename, 0777);
				
				Lumine_Log::debug('Arquivo para a classe '.$classname .' gerado com sucesso');
			} else {
				Lumine_Log::error('O PHP nao tem direito de escrita na pasta "'.$fullpath . '". Verifique se o diretario existe e se o PHP tem direito de escrita.');
				exit;
			}
		}
		
		//// cria os dtos
		if($this->cfg->getOption('create_dtos')){
			reset($this->dtos);
			
			// pasta raiz dos DTO's
			$path = $this->cfg->getProperty('class_path')
				. DIRECTORY_SEPARATOR
				. str_replace('.',DIRECTORY_SEPARATOR,$this->cfg->getProperty('package'))
				. DIRECTORY_SEPARATOR . 'dto';
			
			// para cada DTO 
			foreach($this->dtos as $obj){
				
				$fullpath = $path
					. DIRECTORY_SEPARATOR
					. str_replace('.',DIRECTORY_SEPARATOR, $obj->getPackage());
				
				// se o diretorio nao existe, tenta criar
				if(!is_dir($fullpath) && $this->cfg->getOption('create_paths') == 1){
					mkdir($fullpath,0777,true) or die('Não foi possivel criar o diretorio: '.$fullpath);
				}
					
				$filename = $fullpath . DIRECTORY_SEPARATOR . $obj->getClassname() . $sufix . '.php';
				file_put_contents($filename, $obj->getContent());
			}
		}
		
		// models
		foreach($this->models as $item){
			Lumine_Log::debug('Criando Model ' . $item->getClassname());
			
			$filename = $item->getFullFileName();
			
			if(!is_dir(dirname($filename)) && $this->cfg->getOption('create_paths') == 1){
				$path = dirname($filename);
				mkdir($path,0777,true) OR die('Não foi possivel criar o diretorio: ' . $path);
			} else if(!is_dir(dirname($filename))){
				$path = dirname($filename);
				Lumine_Log::error('Nao eh possivel gravar em '. $path.'. Verifique se a pasta existe e se ha permissao de gravacao' );
			}
			
			$content = $item->getContent();
			file_put_contents($filename, $content);
			chmod($filename,0777);
		}
		
		// copia o arquivo de contexto
		if($this->cfg->getOption('model_context') == 1){
			$contextFile = LUMINE_INCLUDE_PATH . '/lib/Templates/ApplicationContext.php';
			
			if(file_exists($contextFile)){
				$path = $this->cfg->getProperty('class_path')
					. DIRECTORY_SEPARATOR 
					. $this->cfg->getOption('model_context_path') . DIRECTORY_SEPARATOR;
				
				if(!is_dir($path)){
					if($this->cfg->getOption('create_paths') == 1){
						mkdir($path,0777,true) or die('Não foi possivel criar o diretorio '.$path);
					} else {
						Lumine_Log::error('Nao foi possivel gravar o contexto na pasta '.$path.'. Verifique se a pasta existe.');
					}
				}
				
				$destino = $path.'Lumine_ApplicationContext.php';
				
				// so copiamos se o arquivo nao existir
				if( !file_exists($destino) ){
					Lumine_Log::debug('Copiando arquivo de contexto: '.$destino);
					copy($contextFile, $destino);
					chmod($path.'Lumine_ApplicationContext.php',0777);
					
				// ja existe, nao copaimos mas avisamos
				} else {
					Lumine_Log::debug('O arquivo "'.$destino.'" ja existe');
					
				}
			}
		}
		
		// escreve os controles
		$path = $this->cfg->getProperty('class_path');
		$path .= DIRECTORY_SEPARATOR . 'controls' . DIRECTORY_SEPARATOR;
		
		if(!file_exists($path) && $this->cfg->getOption('create_paths') == 1){
			mkdir($path,0777,true) OR die('Nao foi possivel criar o diretorio: ' . $path);
		}
		
		foreach($this->controls as $classname => $content)
		{
			$filename = $path . $classname . '.php';
			$fp = @fopen($filename, 'w');
			if(! $fp)
			{
				Lumine_Log::error('O PHP nao tem direito de escrita para gerar o arquivo "'.$filename . '". Verifique se o diretorio existe e se o PHP tem direito de escrita.');
				exit;
			} else {
				fwrite($fp, $content);
				fclose($fp);
				Lumine_Log::debug('Arquivo de controle "'.$filename . '" gerado com sucesso.');
			}
		}
		
		// copia os demais arquivos
		if(!empty($this->controls) && $this->cfg->getOption('create_controls') != ''){
			$class = 'Lumine_Form_' . $this->cfg->getOption('create_controls');
			
			$ref = new ReflectionClass($class);
			$instance = $ref->newInstance(null);
			
			$instance->copyFiles($path);
		}

		// escreve o arquivo de configuracao
		$filename = $this->cfg->getProperty('class_path').DIRECTORY_SEPARATOR.'lumine-conf.php';
		
		$fp = @fopen($filename, 'w');
		if(!$fp)
		{
			Lumine_Log::error('O PHP nao tem direito de escrita para gerar o arquivo "'.$filename . '". Verifique se o diretorio existe e se o PHP tem direito de escrita.');
			exit;
		}
		
		fwrite($fp, $this->config);
		fclose($fp);
		Lumine_Log::debug('Arquivo "'.$filename . '" gerado com sucesso.');
		
	}
	
	/**
	 * Cria os arquivo de configuracao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	private function createConfigurationsFile()
	{
		$cfg = new Lumine_Reverse_ConfigurationTemplate( $this->original_options );
		$this->config = $cfg->getGeneratedFile();
	}
	
	/**
	 * cria os controles basicos
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $controlName Nome do controle
	 * @return void
	 */
	private function createControls( $controlName )
	{
		$clname = 'Lumine_Form_'.$controlName;
		Lumine::load('Form_'.$controlName);
		
		$clControls = new $clname( null );
		
		reset($this->files);
		foreach($this->files as $classname => $content)
		{
			$this->controls[ $classname ] = $clControls->getControlTemplate($this->cfg, $classname);
		}
		
	}

}

