<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(!class_exists('Annotation', false)){
    require dirname(__FILE__) . '/addendun/annotations.php';
}

/**
 * 
 *
 * @author Hugo Ferreira da Silva
 */
class Lumine_Annotations_Annotations {
    
}


////////////////////////////////////////////////////////////////////
// Anotacoes para as classes
////////////////////////////////////////////////////////////////////
class LumineId extends Annotation {
    
}

class LumineEntity extends Annotation {
    public $package;
}

class LumineTable extends Annotation {
    public $name;
    public $schema;
}

class LumineColumn extends Annotation {
    public $name;
    public $column;
    public $type;
    public $length;
    public $options = array();
}

class LumineOneToMany extends Annotation {
    public $name;
    public $class;
    public $linkOn;
    public $lazy = false;
}

class LumineManyToOne extends Annotation {
    public $class;
    public $linkOn;
    public $onDelete;
    public $onUpdate;
    public $lazy = false;
}

class LumineManyToMany extends Annotation {
    public $name;
    public $class;
    public $linkOn;
    public $column;
    public $table;
    public $lazy = false;
}

class LumineTransient extends Annotation{}