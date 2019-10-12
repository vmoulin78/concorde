<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| The Data Conversion for Artefact
|--------------------------------------------------------------------------
|
| List all the database tables and define the type of each field
|
| Example:
|
| $config['artefact_data_conv'] = array(
|     'folder' => array(
|         'id'         => 'pk',
|         'name'       => 'string',
|         'parent_id'  => 'fk:folder',
|     ),
|     'blg_status' => array(
|         'id'     => 'pk',
|         'name'   => 'string',
|         'color'  => 'string',
|     ),
|     'person' => array(
|         'id'          => 'pk',
|         'username'    => 'string',
|         'password'    => 'string',
|         'phones'      => 'string[]',
|         'created_at'  => 'timestamptz',
|     ),
|     'author' => array(
|         'id'        => 'pk_fk:person',
|         'is_admin'  => 'bool',
|     ),
|     'commentator' => array(
|         'id'     => 'pk_fk:person',
|         'email'  => 'string',
|     ),
|     'article' => array(
|         'id'          => 'pk',
|         'status'      => 'enum_model_id:blg_status',
|         'author_id'   => 'fk:author',
|         'folder_id'   => 'fk:folder',
|         'created_at'  => 'timestamptz',
|     ),
|     'title' => array(
|         'id'          => 'pk',
|         'content'     => 'string',
|         'article_id'  => 'fk:article',
|     ),
|     'paragraph' => array(
|         'id'          => 'pk',
|         'content'     => 'string',
|         'position'    => 'int',
|         'article_id'  => 'fk:article',
|     ),
|     'comment' => array(
|         'id'          => 'pk',
|         'content'     => 'string',
|         'article_id'  => 'fk:article',
|         'person_id'   => 'fk:person',
|         'created_at'  => 'timestamptz',
|     ),
|     'tag' => array(
|         'id'       => 'pk',
|         'content'  => 'string',
|     ),
|     'discount' => array(
|         'id'          => 'pk',
|         'code_name'   => 'string',
|         'start_date'  => 'date',
|         'end_date'    => 'date',
|     ),
|     'article_tag' => array(
|         'article_id'  => 'pk_fk:article',
|         'tag_id'      => 'pk_fk:tag',
|         'created_at'  => 'timestamptz',
|     ),
|     'discount_folder_person' => array(
|         'discount_id'  => 'pk_fk:discount',
|         'folder_id'    => 'pk_fk:folder',
|         'person_id'    => 'pk_fk:person',
|         'created_at'   => 'timestamptz',
|     ),
| );
|
*/
$config['artefact_data_conv'] = array();

/*
|--------------------------------------------------------------------------
| The Mapping Models for Artefact
|--------------------------------------------------------------------------
|
| List the models present in the folder ./application/business/models and related to a database table
|
| Example:
|
| $config['artefact_mapping_models'] = array(
|     'Article'      => [],
|     'Author'       => [],
|     'Comment'      => [],
|     'Commentator'  => [],
|     'Discount'     => [],
|     'Folder'       => [],
|     'Paragraph'    => [],
|     'Person'       => [],
|     'Status'       => ['table' => 'blg_status'], // Here, we set the 'table' key because the table name doesn't match the class name
|     'Tag'          => [],
|     'Title'        => [],
| );
|
*/
$config['artefact_mapping_models'] = array();

/*
|--------------------------------------------------------------------------
| The Mapping Associations for Artefact
|--------------------------------------------------------------------------
|
| List the associations between the mapping models
|
| Example:
|
| $config['artefact_mapping_associations'] = array(
|     array(
|         'associates' => array(
|             array(
|                 'model'      => 'Article',
|                 'property'   => 'title',
|                 'dimension'  => 'one',
|             ),
|             array(
|                 'model'      => 'Title',
|                 'property'   => 'article',
|                 'dimension'  => 'one',
|                 'field'      => 'article_id',
|             ),
|         ),
|     ),
|     array(
|         'associates' => array(
|             array(
|                 'model'      => 'Article',
|                 'property'   => 'paragraphs',
|                 'dimension'  => 'many',
|             ),
|             array(
|                 'model'      => 'Paragraph',
|                 'property'   => 'article',
|                 'dimension'  => 'one',
|                 'field'      => 'article_id',
|             ),
|         ),
|     ),
|     array(
|         'associates' => array(
|             array(
|                 'model'      => 'Article',
|                 'property'   => 'author',
|                 'dimension'  => 'one',
|                 'field'      => 'author_id',
|             ),
|             array(
|                 'model'      => 'Author',
|                 'property'   => 'articles',
|                 'dimension'  => 'many',
|             ),
|         ),
|     ),
|     array(
|         'associates' => array(
|             array(
|                 'model'      => 'Article',
|                 'property'   => 'comments',
|                 'dimension'  => 'many',
|             ),
|             array(
|                 'model'      => 'Comment',
|                 'property'   => 'article',
|                 'dimension'  => 'one',
|                 'field'      => 'article_id',
|             ),
|         ),
|     ),
|     array(
|         'class'       => 'Article_Tag',
|         'associates'  => array(
|             array(
|                 'model'             => 'Article',
|                 'property'          => 'tags',
|                 'dimension'         => 'many',
|                 'joining_field'     => 'article_id',
|                 'reverse_property'  => 'article',
|             ),
|             array(
|                 'model'             => 'Tag',
|                 'property'          => 'articles',
|                 'dimension'         => 'many',
|                 'joining_field'     => 'tag_id',
|                 'reverse_property'  => 'tag',
|             ),
|         ),
|     ),
|     array(
|         'associates' => array(
|             array(
|                 'model'      => 'Person',
|                 'property'   => 'comments',
|                 'dimension'  => 'many',
|             ),
|             array(
|                 'model'      => 'Comment',
|                 'property'   => 'person',
|                 'dimension'  => 'one',
|                 'field'      => 'person_id',
|             ),
|         ),
|     ),
|     array(
|         'associates' => array(
|             array(
|                 'model'      => 'Folder',
|                 'property'   => 'subfolders',
|                 'dimension'  => 'many',
|             ),
|             array(
|                 'model'      => 'Folder',
|                 'property'   => 'parent_folder',
|                 'dimension'  => 'one',
|                 'field'      => 'parent_id',
|             ),
|         ),
|     ),
|     array(
|         'associates' => array(
|             array(
|                 'model'      => 'Folder',
|                 'property'   => 'articles',
|                 'dimension'  => 'many',
|             ),
|             array(
|                 'model'      => 'Article',
|                 'property'   => 'folder',
|                 'dimension'  => 'one',
|                 'field'      => 'folder_id',
|             ),
|         ),
|     ),
|     array(
|         'class'       => 'Discount_Folder_Person',
|         'associates'  => array(
|             array(
|                 'model'             => 'Discount',
|                 'property'          => 'orders',
|                 'dimension'         => 'many',
|                 'joining_field'     => 'discount_id',
|                 'reverse_property'  => 'discount',
|             ),
|             array(
|                 'model'             => 'Folder',
|                 'property'          => 'orders',
|                 'dimension'         => 'many',
|                 'joining_field'     => 'folder_id',
|                 'reverse_property'  => 'folder',
|             ),
|             array(
|                 'model'             => 'Person',
|                 'property'          => 'orders',
|                 'dimension'         => 'many',
|                 'joining_field'     => 'person_id',
|                 'reverse_property'  => 'person',
|             ),
|         ),
|     ),
| );
|
*/
$config['artefact_mapping_associations'] = array();
