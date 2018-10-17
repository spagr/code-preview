<?php declare(strict_types=1);

namespace App\Modules\Core\Model\Product;

use App\Modules\Core\Model\ProductCategoryModel;
use App\Modules\Core\Model\Product\ProductModel;
use Nette\Database\Table\Selection;

/**
 * Find product parameters from category linking
 * Class ProductParametersFinder
 * @package App\Modules\Core\Model\Product
 */
final class ProductParametersFinder
{

	/**
	 * @var ProductCategoryModel
	 */
	private $productCategoryModel;
	/**
	 * @var ProductModel
	 */
	private $productModel;

	/** @var array For detect product type monochrome/color set */
	private $requiredColorIds =  [10, 30, 40, 50];

	// TODO: Move to constructor to get from neon parameters
	private $sortingIdFilter = ['sorting_id' => 3];

	public function __construct(
		ProductCategoryModel $productCategoryModel,
		ProductModel $productModel
	)
	{
		$this->productCategoryModel = $productCategoryModel;
		$this->productModel = $productModel;
	}


	public function getRelatedProducts(int $productId) : Selection
	{
		return $this->getRelatedProductsFromPrinters(
			$this->getLinkedPrinters($productId)
		);
	}

	public function getLinkedPrinters(int $productId): Selection
	{
		return $this->productCategoryModel->findBy(
			'product_id',
			$productId,
			$this->sortingIdFilter
		);
	}

	private function getRelatedProductsFromPrinters(Selection $printersSelection) : Selection
	{
		$productIds = $this->productCategoryModel->findBy(
			'category_id',
			$printersSelection->select('DISTINCT category_id'),
			$this->sortingIdFilter
		)
			->select('DISTINCT product_id');

		return $this->productModel->findBy('id', $productIds);
	}

	public function getRelatedProductsColors(int $productId) : array
	{
		return $this->getRelatedProducts($productId)
			->select('DISTINCT color_id')
			->fetchAssoc('[]=color_id');
	}

	public function isFromColorSet(int $productId) : bool
	{
		return $this->requiredColorIds === array_intersect($this->requiredColorIds, $this->getRelatedProductsColors($productId));
	}
}
