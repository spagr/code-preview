<?php
/**
 * Created by PhpStorm.
 * User: Spagr
 * Date: 16.10.2018
 * Time: 9:08
 */

namespace App\Modules\Core\Model\Product;


use App\Modules\Core\Model\Collection\Collection;

class ProductCollection extends Collection
{
	public function __construct()
	{
		parent::__construct(ProductEntity::class);
	}
}