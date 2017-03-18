<?php
/**
 * Geracao de formularios basicos para Lumine
 * 
 * @package Lumine_Form
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

// carrega a interface
Lumine::load('Form_IForm');

/**
 * Geracao de formularios basicos para Lumine
 * 
 * @package Lumine_Form
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Form_White extends Lumine_EventListener implements Lumine_Form_IForm
{
	/**
	 * Objeto a ser usado
	 * @var Lumine_Base
	 */
    private $obj;
    /**
     * flag para saber se eh para remover as contra-barras dos valores
     * @var boolean
     */
    private $strip_slashes;
    /**
     * Pasta onde estao os arquivos de template
     * @var string
     */
    private $template = '/lib/Templates/white/';

    /**
     * Construtor
     *
     * @param Lumine_Base $obj Objeto Lumine_Base para montar o formulario
     * @author Hugo Ferreira da Silva
     * @return Lumine_Form_IForm
     */
    function __construct(Lumine_Base $obj = null)
    {
        $this->obj = $obj;
        $this->strip_slashes = get_magic_quotes_gpc();
        if(function_exists('date_default_timezone_set')){
        	date_default_timezone_set('America/Sao_paulo');
        }
    }

    /**
     * 
     * @see Lumine_Form_IForm::createForm()
     */
    public function createForm( $action = null )
    {
        $obj = &$this->obj;
        
        ob_start();
        require_once LUMINE_INCLUDE_PATH . $this->template . 'modelo.php';
        
        $form = ob_get_contents();
        ob_end_clean();
        
        return $form;
    }

    /**
     * 
     * @see Lumine_Form_IForm::getInputFor()
     */
    public function getInputFor($nome) {
        
    }
    
    /**
     * 
     * @see Lumine_Form_IForm::getCalendarFor()
     */
     public function getCalendarFor($name) {
        
     }
     
     /**
      * 
      * @see Lumine_Form_IForm::showList()
      */
    public function showList($offset, $limit, $formAction = null, $fieldSort = null, $order = null) {
    	return '';
    	
        if(is_null($formAction))
        {
            $formAction = $_SERVER['PHP_SELF'];
        }
        
        $def = $this->obj->metadata()->getFields();
        foreach($def as $name => $prop)
        {
            if(empty($prop['options']['label']))
            {
                $prop['options']['label'] = ucfirst($name);
            }
            $def[ $name ] = $prop;
        }
        
        $obj = $this->obj;
        $obj->reset();
        $obj->alias('o');
        
        // aplicando os filtros (podemos filtrar por qualquer campo)
        reset($def);
        $rel = 0;
        $pre = 'r';
        
        foreach($def as $name => $prop)
        {
            if( !empty($prop['options']['foreign']) && !empty($prop['options']['displayField']))
            {
                $class = new $prop['options']['class'];
                $obj->join($class->alias($pre.($rel++)),'LEFT');
                $obj->select($class->alias().'.' . $prop['options']['displayField'] .' as '.$name.'_'.$prop['options']['displayField']);
                $obj->selectAs();
                
                if(!empty($_GET[$name.'_filter_']))
                {
                    $obj->where($class->alias().'.'.$prop['options']['displayField'].' like ?', $_GET[$name.'_filter_']);
                }
                
            } else {
                $obj->select('o.'.$name);
                if(array_key_exists($name.'_filter_', $_GET) && $_GET[$name.'_filter_'] !== '')
                {
                    $obj->where('o.'.$name.' like ?', $_GET[$name.'_filter_']);
                }
            }
        }

        $total = $obj->count();
        $obj->limit($offset, $limit);
        $obj->find();
        
        $list = $obj->allToArray();
        
        ob_start();
        require_once LUMINE_INCLUDE_PATH . $this->template . 'edit_list.php';
        
        $form = ob_get_contents();
        ob_end_clean();
        
        return $form;
    }
    
    /**
     * 
     * @see Lumine_Form_IForm::handleAction()
     */
    public function handleAction($actionName, array $values)
    {
        switch($actionName)
        {
            case 'save':
                return $this->save( $values );
            break;
            
            case 'insert':
                return $this->insert( $values );
            break;
            
            case 'delete':
                return $this->delete( $values );
            break;
            
            case 'edit':
                $obj = $this->obj;
                $pks = $obj->metadata()->getPrimaryKeys();
                $obj->reset();
                //$obj->alias('o');
                
                foreach($pks as $pk)
                {
                    $obj->where('{'.$pk['name'].'} = ?', !isset($values['_pk_' . $pk['name']]) ? '' : $values['_pk_' . $pk['name']]);
                }
                
				if($obj->find( true ) > 0) {
					$_POST = $obj->toArray();
					
					$list = $obj->metadata()->getRelations(FALSE);
					
					foreach($list as $name => $item){
						$itens = $obj->fetchLink($name);
						
						foreach($itens as $ref){
							$pk = $ref->metadata()->getPrimaryKeys();
							if(!empty($pk)){
								$_POST[$name][] = $ref->$pk[0]['name'];
							}
						}
					}
					
                }
                
            break;
        }
        return false;
    }
    
   /**
    * 
    * @see Lumine_Form_IForm::getControlTemplate()
    */
    public function getControlTemplate(Lumine_Configuration $cfg, $className ) {
        $file = LUMINE_INCLUDE_PATH . $this->template . 'control.txt';
        if( !file_exists($file))
        {
            Lumine_Log::error('O arquivo "'.$file.'" nao existe!');
            exit;
        }
        
        $content = file_get_contents($file);
        $content = str_replace('{class_path}', str_replace('\\','/',$cfg->getProperty('class_path')), $content);
        $content = str_replace('{entity_name}', $className, $content);
        $content = str_replace('{LUMINE_PATH}', LUMINE_INCLUDE_PATH, $content);
        
        return $content;
    }
    
    /**
     * 
     * @see Lumine_Form_IForm::getTop()
     */
    public function getTop()
    {
        //include_once LUMINE_INCLUDE_PATH . $this->template . 'topo.php';
    }
    
    /**
     * 
     * @see Lumine_Form_IForm::getFooter()
     */
    public function getFooter()
    {
    }
    
    /**
     * Atualiza os dados do objeto no banco de dados
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param array $values Valores vindos do banco de dados
     * @return boolean 
     */
    private function save( $values )
    {
        // pega a lista de chaves primarias e seus valores originais
        // porque em alguns casos a pessoa podera mudar as chaves primarias
        $obj = $this->obj;
        $obj->reset();
        //$obj->alias('o');
        
        $pk_list = $obj->metadata()->getPrimaryKeys();
        foreach($pk_list as $pk)
        {
            // condicao para atualizar
            $obj->where('{'.$pk['name'] .'} = ?', $values['_pk_' . $pk['name'] ]);
        }
        
        $obj->find(true);
        
        // pega os valores da matriz
        $def = $obj->metadata()->getFields();
        foreach($def as $name => $prop)
        {
        	//se for primary key e autoincrement pula
        	if(!empty($prop['options']['primary']) && !empty($prop['options']['autoincrement'])){
        		continue;
        	}
        	
        	if(empty($values['_null_']) || !array_key_exists($name, $values['_null_'])){
	            
        		if( !empty($prop['options']['foreign']) && empty($values[ $name ]))
	            {
	                $obj->$name = null;
	            } else {
	                if($this->strip_slashes)
	                {
		                $obj->$name = stripcslashes($values[$name]);
	                } else {
		                $obj->$name = $values[$name];
	                }
	            }
	            
        	} else {
                $obj->$name = null;
        	}
        }
        // atualiza as referencias MTM
        $def = $obj->metadata()->getRelations(FALSE);
        
        foreach($def as $name => $prop){
        	if($prop['type'] == Lumine_Metadata::MANY_TO_MANY){
        		$obj->removeAll($name);
        		if(!empty($values[$name])){
        			$obj->$name = $values[$name];
        		}
        	}
        }
        
        // atualiza (pelo menos tenta)
        $obj->save( true );
       
        return true;
    }
    
    /**
     * Remove registros 
     * 
     * @author Luiz Fernando M. de Carvalho
     * @param array $values
     * @return boolean
     */
    private function delete( $values )
    {
        // pega a lista de chaves primarias e seus valores originais
        // porque em alguns casos a pessoa podera mudar as chaves primarias
        $obj = $this->obj;
        $obj->reset();
        //$obj->alias('o');

        $pk_list = $obj->metadata()->getPrimaryKeys();
        foreach($pk_list as $pk)
        {
            // condicao para atualizar
            $obj->where('{'.$pk['name'] .'} = ?', $values['_pk_' . $pk['name'] ]);
        }

        // pega os valores da matriz
        $def = $obj->metadata()->getFields();
        foreach($def as $name => $prop)
        {
            if( !empty($prop['options']['foreign']) && empty($values[ $name ]))
            {
                $obj->setFieldValue($name, null);
            } else {
                if($this->strip_slashes)
                {
                    $obj->setFieldValue($name, stripslashes(@$values[ $name ]));
                } else {
                    $obj->setFieldValue($name, @$values[ $name ]);
                }
            }
        }

        // deleta
        $obj->delete( true );
        return true;
    }
    
    /**
     * Insere o registro enviado no banco de dados
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param array $values valores do formulario
     * @return array resultado da validacao
     */
    private function insert( $values )
    {
        $def = $this->obj->metadata()->getFields();
        foreach($def as $name => $prop)
        {
            if( !empty($prop['options']['foreign']) && empty($values[ $name ]))
            {
                $this->obj->setFieldValue($name,null);
            } else {
                if($this->strip_slashes)
                {
	                $this->obj->setFieldValue($name, stripslashes(@$values[$name]));
                } else {
	                $this->obj->setFieldValue($name, @$values[$name]);
                }
            }
        }
        
        // limpamos as pk's que sao auto-incrementaveis
        $obj = &$this->obj;
        $pks = $obj->metadata()->getPrimaryKeys();
        
        foreach($pks as $name => $item){
        	if(!empty($item['options']['autoincrement'])){
        		$obj->$item['name'] = null;
        	}
        }
        
    	// atualiza as referencias MTM
        $def = $obj->metadata()->getRelations(FALSE);
        foreach($def as $name => $prop){
        	if($prop['type'] == Lumine_Metadata::MANY_TO_MANY){
        		$obj->removeAll($name);
        		if(!empty($values[$name])){
        			foreach($values[$name] as $id){
        				$obj->{$name}[] = $id;
        			}
        		}
        	}
        }
        
        // $this->obj->populateFrom($values);
        $res = $this->obj->validate();
        
        if($res === true)
        {
            $this->obj->insert();
            return true;
        }
        return $res;
    }
    
    /**
     * (non-PHPdoc)
     * @see Lumine_Form_IForm::copyFiles()
     */
    public function copyFiles($destination){
    	$dir = LUMINE_INCLUDE_PATH . $this->template;
    	
    	$dh = opendir($dir);
    	while( ($file = readdir($dh)) !== false ){
    		if(preg_match('@\.(js|gif|jpg|jpeg|css|png)$@i',$file)){
    			copy($dir.'/'.$file, $destination . '/' . $file);
    		}
    	}
    	closedir($dh);
    	
    	// agora vamos copiar a pasta de ui
    	$sources = array(
    		'ui/css/cupertino',
    		'ui/css/cupertino/images',
    		'ui/js',
    	);
    	
    	// para cada pasta desejada
    	foreach($sources as $source){
    		$fullsource = LUMINE_INCLUDE_PATH . '/lib/' . $source . '/';
    		$fulldestination = $destination . $source . '/';
    		
    		if(!is_dir($fulldestination)){
    			mkdir($fulldestination, 0777, true) or die('Nao pode criar o diretorio ' . $fulldestination);
    		}
    		
    		
    		$dh = opendir($fullsource);
	    	while( ($file = readdir($dh)) !== false ){
	    		if(preg_match('@\.(js|gif|jpg|jpeg|css|png)$@i',$file)){
	    			copy($fullsource.'/'.$file, $fulldestination . '/' . $file);
	    		}
	    	}
	    	closedir($dh);
    	}
    	
    }
}



