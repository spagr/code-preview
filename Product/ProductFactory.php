<?php  declare(strict_types=1);

namespace App\Modules\Core\Model\Product;

use App\Modules\Core\Model\Product\ProductModel;
use App\Modules\Front\Model\StockService;
use Nette\Application\LinkGenerator;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


/**
 * Class ProductFactory
 */
class ProductFactory
{

	/**
	 * @var StockService
	 */
	private $stockService;
	/**
	 * @var LinkGenerator
	 */
	private $linkGenerator;
	/**
	 * @var ProductModel
	 */
	private $productModel;
	/**
	 * @var ProductParametersFinder
	 */
	private $productParametersFinder;

	public function __construct(
		ProductModel $productModel,
		StockService $stockService,
		LinkGenerator $linkGenerator,
		ProductParametersFinder $productParametersFinder
	)
	{
		$this->stockService = $stockService;
		$this->linkGenerator = $linkGenerator;
		$this->productModel = $productModel;
		$this->productParametersFinder = $productParametersFinder;
	}

	public function create(ActiveRow $productRow) : ProductEntity
	{
		$product = new ProductEntity(
			$productRow,
			$this->stockService,
			$this->productParametersFinder
		);
		$product->link = $this->linkGenerator->link('Front:Catalog:detail', ['productId' => $product->id]);
		return $product;
	}

	public function fromSelection(Selection $selection) : ProductCollection
	{
		$collection = new ProductCollection();
		foreach ($selection as $row) {
			$collection->add($this->create($row));
		}
		return $collection;
	}

	public function getModel(): ProductModel
	{
		return $this->productModel;
	}
}