<?php
/*-----------------------------------------------------------------------------
 Modelos de configuracao disponiveis.
 Sao facilitadores para que o usuario possa de maneira rapida criar
 a estrutura de desenvolvimento sem muito conhecimento da ferramenta. 
-----------------------------------------------------------------------------*/

// lista de templates
$configTemplates = array();

// Modelo do CodeIgniter
$configTemplates['ci'] = array(
		'name' => 'CodeIgniter With MySQL',
		'template' => array(
					'dialect' => 'MySQL',
					'package' => 'system.application.models.dao',
					'host' => 'localhost',
					'port' => 3306,
					'user' => 'root',
					'class_path' => str_replace('\\', '/', dirname(LUMINE_INCLUDE_PATH)),
					
					'options' => array(
						'create_paths' => 1,
						'camel_case' => 1,
						'usar_dicionario' => 1,
						'many_to_many_style' => '%s_%s',
						'create_entities_for_many_to_many' => 0,
						'tipo_geracao' => 1,
						'keep_foreign_column_name' => 1,
						'remove_count_chars_start' => 0,
						'remove_count_chars_end' => 0,
						'dto_package' => array('entidades'),
						'dto_format' => '%sDTO',
						'create_controls' => 'White',
						'create_models' => 1,
						'model_path' => 'system/application/models',
						'model_format' => '%sModel',
						'model_context' => 1,
						'model_context_path' => 'system/application/libraries'
					)
				)
			);

$configTemplates['pgsql'] = array(
		'name' => 'PostgreSQL Template',
		'template' => array(
					'dialect' => 'PostgreSQL',
					'package' => 'system.entidades',
					'host' => 'localhost',
					'port' => 5432,
					'user' => 'postgres',
					'class_path' => str_replace('\\', '/', dirname(LUMINE_INCLUDE_PATH)),
					
					'options' => array(
						'create_paths' => 1,
						'camel_case' => 1,
						'usar_dicionario' => 1,
						'many_to_many_style' => '%s_%s',
						'create_entities_for_many_to_many' => 0,
						'tipo_geracao' => 1,
						'keep_foreign_column_name' => 1,
						'remove_count_chars_start' => 0,
						'remove_count_chars_end' => 0,
						'dto_package' => array('entidades'),
						'dto_format' => '%sDTO',
						'create_controls' => 'White',
						'create_models' => 0,
						'model_path' => '',
						'model_format' => '%sModel',
						'model_context' => 1,
						'model_context_path' => ''
					)
				)
			);

