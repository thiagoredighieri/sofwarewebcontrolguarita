<?php

Lumine::load('Lumine_Annotations_Annotations');

class Lumine_Descriptor_Annotations extends Lumine_Descriptor_AbstractDescriptor {

	public function parse(){
		$map = array();
		$map['time'] = $this->getModificationTime();
		$map['fields'] = array();
		$map['relations'] = array();

		// pegando as anotacoes da classe
		$ref = new ReflectionAnnotatedClass($this->getClassname());

		if(!$ref->hasAnnotation('LumineEntity')){
			return;
		}

		$map['package'] = $ref->getAnnotation('LumineEntity')->package;

		if($ref->hasAnnotation('LumineTable')){
			$map['tablename'] =	$ref->getAnnotation('LumineTable')->name;
		} else {
			$map['tablename'] = strtolower($this->getClassname());
		}

		// pegando as propriedades
		$props = $ref->getProperties();

		/** @var $prop ReflectionAnnotatedProperty */
		foreach($props as $prop){
			if(!$prop->hasAnnotation('LumineTransient')){

				if($prop->hasAnnotation('LumineColumn')){
					$anno = $prop->getAnnotation('LumineColumn');
					if($prop->hasAnnotation('LumineId')){
						$anno->options['primary'] = true;
					}

					if($prop->hasAnnotation('LumineManyToOne')){
						$mto = $prop->getAnnotation('LumineManyToOne');
						$anno->options['class'] = $mto->class;
						$anno->options['linkOn'] = $mto->linkOn;
						$anno->options['onUpdate'] = $mto->onUpdate;
						$anno->options['onDelete'] = $mto->onDelete;
						$anno->options['lazy'] = $mto->lazy;
						$anno->options['foreign'] = true;
					}

					if(empty($anno->name)){
						$anno->name = $prop->getName();
					}

					if(empty($anno->column)){
						$anno->column = $prop->getName();
					}
						
						
					$map['fields'][] = array(
						$anno->name, $anno->column
						, $anno->type, $anno->length
						, empty($anno->options) ? array() : $anno->options
					);

				} else if($prop->hasAnnotation('LumineManyToMany')) {
					$anno = $prop->getAnnotation('LumineManyToMany');

					if(empty($anno->name)){
						$anno->name = $prop->getName();
					}

					$map['relations'][] = array(
						$anno->name, Lumine_Metadata::MANY_TO_MANY
						, $anno->class, $anno->linkOn
						, $anno->table, $anno->column
						, $anno->lazy
					);

				} else if($prop->hasAnnotation('LumineOneToMany')) {
					$anno = $prop->getAnnotation('LumineOneToMany');

					if(empty($anno->name)){
						$anno->name = $prop->getName();
					}

					$map['relations'][] = array(
						$anno->name, Lumine_Metadata::ONE_TO_MANY
						, $anno->class, $anno->linkOn
						, null, null
						, $anno->lazy
					);

				} else if($prop->getDeclaringClass()->getName() == $this->getClassname()) {
					$map['fields'][] = array(
						$prop->getName()
						, $prop->getName()
						, 'varchar'
						, 255
						, array()
					);
				}
			}
		}
		
		return $map;
	}
}