<?php

/**
 * Classe para gerar as entidades atraves da engenharia reversa
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 */

class Lumine_Reverse_ClassTemplate
{

    /**
     * armazena as chaves estrangeiras
     *
     * @var array
     */
    private $foreign      = array();
    /**
     * nome da classe
     *
     * @var string
     */
    private $classname;
    /**
     * nome da tabela
     *
     * @var string
     */
    private $tablename;
    /**
     * nome do pacote
     *
     * @var sting
     */
    private $package;
    /**
     * descricao da classe (campos)
     *
     * @var array
     */
    private $description  = array();

    /**
     * relacionamentos do tipo um-para-muitos
     *
     * @var array
     */
    private $one_to_many  = array();
    /**
     * relacionamentos muitos-para-muitos
     *
     * @var unknown_type
     */
    private $many_to_many = array();

    /**
     * delimitador inicial
     *
     * @var string
     */
    private $init_delim   = "#### START AUTOCODE";
    /**
     * delimitar final
     *
     * @var string
     */
    private $end_delim    = "#### END AUTOCODE";

    /**
     * dialeto usado
     *
     * @var string
     */
    private $dialect      = null;

    /**
     * Utilizar ou nao CamelCase nos nomes das propriedades
     *
     * @var unknown_type
     */
    private $useCamelCase = true;

    /**
     * Gerar get/set
     * @var boolean
     */
    private $generateAccessors = false;

    /**
     * Verifica se um nome ja esta na lista, para evitar duplicar e dar problemas
     * @var array
     */
    private $namesList = array();
    
    /**
     * Formatador de saida
     * @var Lumine_Templates_AbstractTemplate
     */
    private $formatter = null;

    /**
     * Construtor
     *
     * @param string $tablename nome da tabela
     * @param string $classname nome da classe a ser criada
     * @param string $package nome do pacote da classe
     */
    function __construct($tablename = null, $classname=null, $package=null)
    {
        $this->setTablename($tablename);
        $this->setClassname($classname);
        $this->setPackage($package);
        $this->setFormatter(new Lumine_Templates_DefaultTemplate());
    }
    

    /**
     * Altera o nome do dialeto
     *
     * @param string $dialect novo dialeto
     */
    public function setDialect( $dialect )
    {
        $this->dialect = $dialect;
    }

    /**
     * altera o nome da tabela
     *
     * @param string $tablename nome da tabela
     */
    public function setTablename( $tablename )
    {
        $this->tablename = $tablename;
    }

    /**
     * altera o nome da classe
     *
     * @param string $classname nome da classe
     */
    public function setClassname( $classname )
    {
        $this->classname = $classname;
    }

    public function setPackage( $package )
    {
        $this->package = $package;
    }

    public function setDescription(array $desc)
    {
        $this->description = $desc;
    }
    public function setForeignKeys(array $foreign)
    {
        $this->foreign = $foreign;
    }

    public function setCamelCase( $camelCase )
    {
        $this->useCamelCase = $camelCase;
    }

    public function getDialect()
    {
        return $this->dialect;
    }
    public function getTablename()
    {
        return $this->tablename;
    }
    public function getClassname()
    {
        if( $this->getCamelCase() == true )
        {
            $str = $this->CamelCase($this->classname);
            return ucfirst($str);
        }
        return $this->classname;
    }
    public function getPackage()
    {
        return $this->package;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getForeignKeys()
    {
        return $this->foreign;
    }

    public function getCamelCase()
    {
        return $this->useCamelCase;
    }

    /**
     * Altera se e para criar ou nao get/set
     * @param boolean $value
     */
    public function setGenerateAccessors($value){
    	$this->generateAccessors = $value;
    }

    /**
     * Recupera se e para criar ou nao get/set
     * @return boolean
     */
    public function getGenerateAccessors(){
    	return $this->generateAccessors;
    }

    /**
     * pega os dados de uma coluna especifica
     *
     * @param string $column nome da coluna
     * @return array Matriz associativa com os dados
     */
    public function getDefColumn( $column )
    {
        reset($this->description);
        foreach($this->description as $def)
        {
            if($def[0] == $column)
            {
                return $def;
            }
            if(!empty($def['options']) && $def['options']['column'] == $column)
            {
                return $def;
            }
        }
    }

    /**
     * Altera os dados de uma coluna
     *
     * @param string $column Nome da coluna
     * @param array $newdef Matriz associativa com os dados
     */
    public function setDefColumn( $column, $newdef)
    {
        reset($this->description);
        foreach($this->description as $item => $def)
        {
            if($def[0] == $column)
            {
                $this->description[ $item ] = $newdef;
                return;
            }
        }

    }

    public function getGeneratedFile()
    {
    	if($this->getFormatter() == null){
	        $str = $this->getTop();
	        $str .= $this->getClassBody();
	        $str .= $this->getFooter();
    	} else {
    		$this->getFormatter()->setClassTemplate($this);
    		$str = $this->getFormatter()->getContents();
    	}

        return $str;
    }

    public function addOneToMany($def)
    {
    	$def['name'] = $this->checkNames($def['name']);
        $this->one_to_many[] = $def;
    }

    public function addManyToMany($def)
    {
    	$def['name'] = $this->checkNames($def['name']);
        $this->many_to_many[] = $def;
    }
    
    /**
     * Retorna a lista de itens one-to-many
     * 
     * @return array
     * @author Hugo Ferreira da Silva
     */
    public function getOneToManyList(){
    	return $this->one_to_many;
    }
    
    /**
     * Retorna a lista de itens many-to-many
     * 
     * @return array
     * @author Hugo Ferreira da Silva
     */
    public function getManyToManyList(){
    	return $this->many_to_many;
    }

    public function getInitDelim()
    {
        return $this->init_delim;
    }

    public function getEndDelim()
    {
        return $this->end_delim;
    }


    public function CamelCase( $name ) {
    	if( $this->getCamelCase() == false ) {
    		return $name;
    	}
    	return Lumine_Util::camelCase($name);
    }

    /**
     * Verifica se existem nomes duplicados
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br
     * @param string $name Nome a ser verificado
     * @return string variavel modificada para manter um unico nome
     */
    private function checkNames($name){
    	if(array_key_exists($name,$this->namesList)){
    		$this->namesList[$name]++;
    		$name .= '_'.$this->namesList[$name];
    	} else {
    		$this->namesList[$name] = 0;

    	}

    	return $name;
    }

    /**
     * 
     * 
     * @return Lumine_Templates_AbstractTemplate
     * @author Hugo Ferreira da Silva
     */
	public function getFormatter()
	{
	    return $this->formatter;
	}

	/**
	 * Altera o formatador
	 * 
	 * @param Lumine_Templates_AbstractTemplate $formatter
	 * @author Hugo Ferreira da Silva
	 */
	public function setFormatter(Lumine_Templates_AbstractTemplate $formatter)
	{
	    $this->formatter = $formatter;
	}
}


