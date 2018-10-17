<?php  declare(strict_types=1);


namespace App\Modules\Core\Model\Product;

use App\Modules\Core\Model\RowWrapper;
use App\Modules\Front\Model\StockService;
use Nette\Database\Table\ActiveRow;


/**
 * Class ProductEntity
 * @package App\Modules\Core\Model\Product
 * @property-read $inStockSum
 * @property-read $supplierStockSum
 */
class ProductEntity extends RowWrapper
{

	/**
	 * @var StockService
	 */
	private $stockService;

	/**
	 * @var ProductParametersFinder
	 */
	private $productParametersFinder;

	public function __construct(
		ActiveRow $row,
		StockService $stockService,
		ProductParametersFinder $productParametersFinder
	)
	{
		parent::__construct($row);
		$this->stockService = $stockService;
		$this->data['inStockSum'] = $this->getInStockSum();
		$this->data['supplierStockSum'] = $this->getSupplierStockSum();
		$this->productParametersFinder = $productParametersFinder;
	}

	private function getInStockSum() : int
	{
		$inStock = 0;
		foreach ($this->stockService->getExpeditionStocks() as $stock) {
			$inStock += $this->row->{'stock' . $stock->id};
		}
		return $inStock;
	}

	public function isInStockTodayExpedition($requireAmount = 1) : bool
	{
		foreach ($this->stockService->getExpeditionStocks() as $stock) {
			if ($this->row->{'stock' . $stock->id} >= $requireAmount) {
				if ($stock->isExpeditionToday()) {
					return true;
				}
			};
		}
		return false;
	}

	private function getSupplierStockSum() : int
	{
		$supplierStock = 0;
		foreach ($this->stockService->getSupplierStocks() as $stock) {
			$supplierStock += $this->row->{'stock' . $stock->id};
		}
		return $supplierStock;
	}

	public function getRelatedProductsColors() : array
	{
		return $this->productParametersFinder->getRelatedProductsColors($this->row->getPrimary());
	}


	public function isFromColorSet() : bool
	{
		return $this->productParametersFinder->isFromColorSet($this->row->getPrimary());
	}
}