<?php

Lumine::load('Lumine_Annotations_Annotations');

/**
 * 
 *
 * @author Hugo Ferreira da Silva
 */
class Lumine_Templates_DefaultTemplate extends Lumine_Templates_AbstractTemplate {
    
	/**
     * nome do autor
     *
     * @var string
     */
    private $author       = 'Hugo Ferreira da Silva';
    /**
     * data de criacao
     *
     * @var unknown_type
     */
    private $date         = null;
    /**
     * nome do gerador
     *
     * @var string
     */
    private $generator    = "Lumine_Reverse";
    /**
     * link padrao da documentacao
     *
     * @var string
     */
    private $link         = 'http://www.hufersil.com.br/lumine';

    /**
     * identacao dos campos
     *
     * @var string
     */
    private $ident        = '    ';
	
	/**
	 * @see Lumine_Templates_AbstractTemplate::getContents()
	 */
	public function getContents(){
		$this->date = date('Y-m-d');
		$str = $this->getTop();
		$str .= $this->getClassBody();
		$str .= $this->getFooter();
		
		return $str;
	}
	
	private function getTop()
	{
		$txt = file_get_contents(LUMINE_INCLUDE_PATH.'/lib/Templates/classes.top.txt');
		$txt = str_replace('{classname}', $this->getClassTemplate()->getClassname(), $txt);
		$txt = str_replace('{tablename}', $this->getClassTemplate()->getTablename(), $txt);
		$txt = str_replace('{package}', $this->getClassTemplate()->getPackage(), $txt);
		$txt = preg_replace('@\{(\w+)\}@e', '$this->$1', $txt);
	
		return $txt;
	}
	
	
	private function getClassBody()
	{
		$txt = file_get_contents(LUMINE_INCLUDE_PATH.'/lib/Templates/classes.body.txt');
	
		//////////////////////////////////////////////////////////
		// membros da classe
		//////////////////////////////////////////////////////////
		if(preg_match('@\{members\}(.*?)\{/members\}@ms', $txt, $reg))
		{
			$itens = array('');
			$line = trim($reg[1]);
	
			foreach($this->getClassTemplate()->getDescription() as $item)
			{
				$model = str_replace('{name}', $this->getClassTemplate()->CamelCase($item[0]), $line);
				$itens[] = $this->ident . $model;
			}
	
			foreach($this->getClassTemplate()->getOneToManyList() as $item)
			{
				$model = str_replace('{name}', $this->getClassTemplate()->CamelCase($item['name']).' = array()', $line);
				$itens[] = $this->ident . $model;
			}
	
			foreach($this->getClassTemplate()->getManyToManyList() as $item)
			{
				$model = str_replace('{name}', $this->getClassTemplate()->CamelCase($item['name']).' = array()', $line);
				$itens[] = $this->ident . $model;
			}
	
			$txt = str_replace($reg[0], implode(PHP_EOL, $itens), $txt);
		}
	
		//////////////////////////////////////////////////////////
		// accessors
		//////////////////////////////////////////////////////////
		if(preg_match('@\{accessors\}(.*?)\{/accessors\}@ms', $txt, $reg))
		{
			$itens = array('');
	
			if($this->getClassTemplate()->getGenerateAccessors()){
				$line = trim($reg[1]);
	
				foreach($this->getClassTemplate()->getDescription() as $item)
				{
					$model = str_replace('{name}', $this->getClassTemplate()->CamelCase($item[0]), $line);
					$model = str_replace('{accessor}', ucfirst($this->getClassTemplate()->CamelCase($item[0])), $model);
					$itens[] = $this->ident . $model;
				}
	
				foreach($this->getClassTemplate()->getOneToManyList() as $item)
				{
					$model = str_replace('{name}', $this->getClassTemplate()->CamelCase($item['name']), $line);
					$model = str_replace('{accessor}', ucfirst($this->getClassTemplate()->CamelCase($item['name'])), $model);
					$itens[] = $this->ident . $model;
				}
	
				foreach($this->getClassTemplate()->getManyToManyList() as $item)
				{
					$model = str_replace('{name}', $this->getClassTemplate()->CamelCase($item['name']), $line);
					$model = str_replace('{accessor}', ucfirst($this->getClassTemplate()->CamelCase($item['name'])), $model);
					$itens[] = $this->ident . $model;
				}
			}
	
			$txt = str_replace($reg[0], implode(PHP_EOL, $itens), $txt);
		}
		//////////////////////////////////////////////////////////
	
		// definicoes
		if(preg_match('@\{definition\}(.*?)\{/definition\}@ms', $txt, $reg))
		{
			$modelo = trim($reg[1]);
			$itens = array('');
	
			foreach($this->getClassTemplate()->getDescription() as $item)
			{
				if(empty($item['options']['column']))
				{
					$column = $item[0];
				} else {
					$column = $item['options']['column'];
				}
	
				$length = empty($item[3]) ? 'null' : $item[3];
				$options = array();
	
				if($item[4] == true)
				{
					$options[] = "'primary' => true";
				}
				if($item[5] == true)
				{
					$options[] = "'notnull' => true";
				}
				if( !is_null($item[6]) )		// if( !empty($item[6]))
				{
					$options[] = "'default' => '".$item[6]."'";
				}
				if( !empty($item[7]))
				{
					$options[] = "'autoincrement' => true";
				}
	
				// adicionado em 01/09/2009 - pega o nome da sequencia
				if(!empty($item[8]) && is_array($item[8])){
					foreach($item[8] as $key => $val){
						$options[] = "'".$key."' => '" . $val . "'";
					}
				}
	
	
				if( !empty($item['options']))
				{
					unset($item['options']['column']);
					foreach($item['options'] as $def => $value)
					{
						if( $def == 'linkOn' )
						{
							$value = $this->getClassTemplate()->CamelCase($value);
						}
						$options[] = "'".$def."' => '".$value."'";
					}
				}
	
				// se tiver valores a mais (geralmente em enum)
				if( preg_match('@^\w+\((.*?)\)$@', $item[2], $reg2) ){
					$_list = array();
					$_str = str_replace('"', '', $reg2[1]);
					$_str = str_replace("'", '', $_str);
					$temps = explode(',', $_str);
					foreach($temps as $temp){
						$_list[] = "'" . $temp . "'";
					}
					$options[] = "'option_list' => array(" . implode(', ', $_list) . ")";
				}
	
				$options_str = 'array('.implode(', ', $options) . ')';
				$type = $this->getClassTemplate()->getDialect()->getLumineType( $item[2] );
	
				$line = $modelo;
				$line = str_replace('{name}',    $this->getClassTemplate()->CamelCase($item[0]),      $line);
				$line = str_replace('{column}',  $column,       $line);
				$line = str_replace('{type}',    addslashes($type), $line);
				$line = str_replace('{length}',  $length,       $line);
				$line = str_replace('{options}', $options_str,  $line);
	
				$itens[] = $this->ident . $this->ident . $line;
			}
	
			$txt = str_replace($reg[0], implode(PHP_EOL, $itens), $txt);
		}
	
		if(preg_match('@\{relations\}(.*?)\{/relations\}@ms', $txt, $reg))
		{
			$modelo = trim($reg[1]);
			$itens = array('');
	
			foreach($this->getClassTemplate()->getOneToManyList() as $otm)
			{
				$name    = $otm['name'];
				$type    = 'ONE_TO_MANY';
				$class   = $otm['class'];
				$linkOn  = $otm['linkOn'];
	
				$line = $modelo;
				$line = str_replace('{name}',        $this->getClassTemplate()->CamelCase($name),      $line);
				$line = str_replace('{type}',        $type,      $line);
				$line = str_replace('{class}',       $class,     $line);
				$line = str_replace('{linkOn}',      $this->getClassTemplate()->CamelCase($linkOn),    $line);
				$line = str_replace('{table_join}',  'null',     $line);
				$line = str_replace('{column_join}', 'null',     $line);
				$line = str_replace('{lazy}',        'null',     $line);
	
				$itens[] = $this->ident . $this->ident . $line;
			}
	
			foreach($this->getClassTemplate()->getManyToManyList() as $mtm)
			{
				$mtm['linkOn']   = $this->getClassTemplate()->CamelCase($mtm['linkOn']);
				$mtm['table_join']   = "'" . $mtm['table_join'] . "'";
				$mtm['column_join']  = "'" . $mtm['column_join'] . "'";
	
				$line = $modelo;
				$line = preg_replace('@\{(\w+)\}@e', '$mtm["$1"]', $line);
	
				$itens[] = $this->ident . $this->ident . $line;
			}
	
			$txt = str_replace($reg[0], implode(PHP_EOL, $itens), $txt);
		}
	
		$txt = str_replace('{classname}', $this->getClassTemplate()->getClassname(), $txt);
		$txt = str_replace('{tablename}', $this->getClassTemplate()->getTablename(), $txt);
		$txt = str_replace('{package}', $this->getClassTemplate()->getPackage(), $txt);
		$txt = preg_replace('@\{(\w+)\}@e', '$this->$1', $txt);
	
		return $txt;
	}
	
	private function getFooter()
	{
		$str = PHP_EOL . '}' . PHP_EOL;
	
		return $str;
	}
	
}

