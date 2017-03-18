<?php

Lumine::load('Lumine_Annotations_Annotations');

/**
 * 
 *
 * @author Hugo Ferreira da Silva
 */
class Lumine_Templates_AnnotationsTemplate extends Lumine_Templates_AbstractTemplate {
    
	private $spaces = '    ';
	
	private $modelo = '<?php
#### START AUTOCODE
/**
 *
 * @LumineEntity(package="<package>")
 * @LumineTable(name="<tablename>")
 */
class <classname> extends Lumine_Base {
	<properties>
	
	<relations>
	
	#### END AUTOCODE
}

';
    
    /**
     * Recupera uma string representando o corpo do arquivo gerado
     * 
     * @author Hugo Ferreira da Silva
     * @return string
     */
    public function getContents(){
        $tpl = $this->modelo;
        
        $fields = array();
        $relations = array();
        
        foreach($this->getClassTemplate()->getDescription() as $item){
        	$fieldName = $this->getClassTemplate()->CamelCase($item[0]);
        	$annotations = array();
        	
        	if($item[4]){
        		$annotations[] = new LumineId();
        	}
        	
        	$anno = new LumineColumn();
        	$anno->column = empty($item['options']['column']) ? $item[0] : $item['options']['column'];
        	$anno->name = $this->getClassTemplate()->CamelCase($item[0]);
        	$anno->length = $item[3];
        	$anno->type = $this->getClassTemplate()->getDialect()->getLumineType($item[2]);
        	
        	if($item[5]){
        		$anno->options['notnull'] = true;
        	}
        	
        	if(!is_null($item[6])) {
        		$anno->options['default'] = $item[6];
        	}
        	
        	if($item[7]){
        		$anno->options['autoincrement'] = true;
        	}
        	
        	// adicionado em 01/09/2009 - pega o nome da sequencia
        	if(!empty($item[8]) && is_array($item[8])){
       			$anno->options = array_merge($anno->options, $item[8]);
        	}
        	
        	// se tiver valores a mais (geralmente em enum)
        	if( preg_match('@^\w+\((.*?)\)$@', $item[2], $reg2) ){
        		$_list = array();
        		$_str = str_replace(array('"',"'"), '', $reg2[1]);
        		$temps = explode(',', $_str);
        		foreach($temps as $temp){
        			$_list[] = "'" . $temp . "'";
        		}
        		$anno->options['option_list'] = $_list;
        	}
        	
        	$annotations[] = $anno;
        	
        	if(!empty($item['options']['foreign'])){
        		$manyToOne = new LumineManyToOne();
        		$manyToOne->class = $item['options']['class'];
        		$manyToOne->linkOn = $this->getClassTemplate()->CamelCase($item['options']['linkOn']);
        		$manyToOne->onDelete = $item['options']['onDelete'];
        		$manyToOne->onUpdate = $item['options']['onUpdate'];
        		$manyToOne->lazy = false;
        		
        		$annotations[] = $manyToOne;
        	}
        	
        	$resultStr = $this->generateFromAnnotations($fieldName, $annotations);
        	
        	if(!empty($resultStr)){
        		$fields[] = $resultStr;
        	}
        }
        
        foreach($this->getClassTemplate()->getOneToManyList() as $item){
        	$anno = new LumineOneToMany();
        	$anno->name = $item['name'];
        	$anno->class = $item['class'];
        	$anno->linkOn = $item['linkOn'];
        	$anno->lazy = false;
        	
        	$resultStr = $this->generateFromAnnotations($item['name'], array($anno));
        	 
        	if(!empty($resultStr)){
        		$relations[] = $resultStr;
        	}
        }
        
        foreach($this->getClassTemplate()->getManyToManyList() as $item){
        	$anno = new LumineManyToMany();
        	$anno->name = $item['name'];
        	$anno->class = $item['class'];
        	$anno->linkOn = $item['linkOn'];
        	$anno->column = $item['column_join'];
        	$anno->table = $item['table_join'];
        	$anno->lazy = false;
        	
        	$resultStr = $this->generateFromAnnotations($item['name'], array($anno));
        	 
        	if(!empty($resultStr)){
        		$relations[] = $resultStr;
        	}
        }
        
        $tpl = str_replace('<classname>', $this->getClassTemplate()->getClassname(), $tpl);
        $tpl = str_replace('<tablename>', $this->getClassTemplate()->getTablename(), $tpl);
        $tpl = str_replace('<package>', $this->getClassTemplate()->getPackage(), $tpl);
        $tpl = str_replace('<properties>', trim(implode(PHP_EOL, $fields)), $tpl);
        $tpl = str_replace('<relations>', trim(implode(PHP_EOL, $relations)), $tpl);
        
        return $tpl;
    }
    
    /**
     * Gera um elemento com as anotacoes passadas
     * 
     * @param string $field
     * @param array $annotations
     * @author Hugo Ferreira da Silva
     * @return string
     */
    private function generateFromAnnotations($field, $annotations){
    	$str = $this->addLine('/**');
    	$str .= $this->addLine(' * Coluna '.$field);
    	
    	foreach($annotations as $anno){
    		$vars = get_object_vars($anno);
    		$name = get_class($anno);
    		
    		$line = ' * @' . $name;
    		
    		if($name != 'LumineId'){
    			$line .= '(';
	    		foreach($vars as $var => $value){
	    			if($var != 'value'){
	    				$opt = $this->parseValue($var, $value);
	    				if(!empty($opt)){
	    					$line .= $opt . ', ';
	    				}
	    			}
	    		}
	    		
	    		$line = substr($line, 0, strlen($line) -2) . ')';
    		}
    		
    		$str .= $this->addLine($line);
    	}
    	
    	$str .= $this->addLine(' */ ');
    	$str .= $this->addLine('public $'.$field.';');
    	
    	return $str;
    }
    
    /**
     * Cria uma linha para colocar na string final
     * 
     * @param string $line
     * @param int $tabs
     * @author Hugo Ferreira da Silva
     * @return string
     */
    private function addLine($line, $tabs = 1){
    	return str_repeat($this->spaces, $tabs) .  $line . PHP_EOL;
    }
    
    /**
     * Faz a analise para retornar a string correta conforme os valores enviados
     * 
     * @param string $key
     * @param mixed $value
     * @author Hugo Ferreira da Silva
     * @return string
     */
    private function parseValue($key, $value){
    	if(is_bool($value)){
    		return $key . '=' . ($value ? 'true' : 'false');
    	} else if(is_null($value)){
    		return $key . '= NULL';
    	} else if(is_numeric($value)) {
    		return $key . '=' . $value;
    	} else if(is_string($value)) {
    		return $key . '="' . $value.'"';
    	} else if(is_array($value) && !empty($value)){
    		$str = is_numeric($key) ? '' : $key .'={';
    		foreach($value as $idx => $val){
    			$str .= $this->parseValue($idx, $val) . ' ,';
    		}
    		
    		$str = substr($str, 0, strlen($str) - 2) . '}';
    		return $str;
    	}
    }
}

