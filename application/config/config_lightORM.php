<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| The Business Compositions for LightORM
|--------------------------------------------------------------------------
|
| Example:
|
| $config['lightORM_business_compositions'] = array(
|     array(
|         'compound_model'       => 'Article',
|         'compound_property'    => 'title',
|         'component_model'      => 'Title',
|         'component_dimension'  => 'one',
|         'component_field'      => 'article_id',
|     ),
|     array(
|         'compound_model'       => 'Article',
|         'compound_property'    => 'paragraphs',
|         'component_model'      => 'Paragraph',
|         'component_dimension'  => 'many',
|         'component_field'      => 'article_id',
|     ),
| );
|
*/
$config['lightORM_business_compositions'] = array();

/*
|--------------------------------------------------------------------------
| The Business Associations for LightORM
|--------------------------------------------------------------------------
|
| Example:
|
| $config['lightORM_business_associations'] = array(
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
|                 'field'      => 'comment_id',
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
| );
|
*/
$config['lightORM_business_associations'] = array();

/*
|--------------------------------------------------------------------------
| The Data Conversion for LightORM
|--------------------------------------------------------------------------
|
| Define the type of each field of the database
|
| Example:
|
| $config['lightORM_data_conv'] = array(
|     'status' => array(
|         'id'     => 'pk',
|         'name'   => 'string',
|         'color'  => 'string',
|     ),
|     'person' => array(
|         'id'          => 'pk',
|         'username'    => 'string',
|         'password'    => 'string',
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
|         'status_id'   => 'enum_model_id:status',
|         'author_id'   => 'fk:author',
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
|     'article_tag' => array(
|         'article_id'  => 'pk_fk:article',
|         'tag_id'      => 'pk_fk:tag',
|     ),
| );
|
*/
$config['lightORM_data_conv'] = array();
