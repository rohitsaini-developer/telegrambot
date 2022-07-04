<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Product extends Model
{
    //

    protected $table = 'products';

    protected $schema = [
        'id'          => 'int',
        'name'        => 'string',
        'code'        => 'string',
        'status'      => 'enum',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];


}
