<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
|     'folder' => array(
|         'id'         => 'pk',
|         'name'       => 'string',
|         'parent_id'  => 'fk:folder',
|     ),
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
|     'article_tag' => array(
|         'article_id'  => 'pk_fk:article',
|         'tag_id'      => 'pk_fk:tag',
|         'created_at'  => 'timestamptz',
|     ),
| );
|
*/
$config['lightORM_data_conv'] = array();

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
| );
|
*/
$config['lightORM_business_associations'] = array();
