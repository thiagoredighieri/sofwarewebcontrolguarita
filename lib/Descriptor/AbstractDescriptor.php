<?php


abstract class Lumine_Descriptor_AbstractDescriptor {
	
	private $classname;
	private $filename;

	public function __construct($classname, $filename){
		$this->classname = $classname;
		$this->filename = $filename;
	}
	
	abstract public function parse();
	
	public function getClassname(){
	    return $this->classname;
	}
	
	public function getFilename(){
	    return $this->filename;
	}
	
	public function getModificationTime(){
		return filemtime($this->getFilename());
	}
	
}