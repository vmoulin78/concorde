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
|     'Article' => array(
|         'title:Title',
|         'paragraphs:Paragraph[]',
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
|     'Article' => array(
|         'author:Author',
|         'comments:Comment[]',
|         'tags:Tag[]',
|     ),
|     'Tag' => array(
|         'articles:Article[]',
|     ),
|     'Author' => array(
|         'articles:Article[]',
|     ),
|     'Person' => array(
|         'comments:Comment[]',
|     ),
|     'Comment' => array(
|         'person:Person',
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
|         'status_id'   => 'enum_model_id:Status',
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
