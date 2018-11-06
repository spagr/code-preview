<?php declare(strict_types=1);

namespace Test;

use Mockery;
use Nette;
use Tester;
use Tester\Assert;


use Nette\Database\Context;
use Nette\Database\Table\Selection;

/** @var  Nette\DI\Container $container */
$container = require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class AbstractBaseModel extends Tester\TestCase
{

	private $contextMock;
	private $abstractBaseModelMock;
	private $returnSelectionMock;
	private $innerSelectionMock;


	public function setUp()
	{
		$this->contextMock = Mockery::mock(Context::class);
		$this->abstractBaseModelMock = Mockery::mock(\App\Modules\Core\Model\AbstractBaseModel::class, [$this->contextMock])
			->makePartial();

		$this->returnSelectionMock = Mockery::mock(Selection::class);

		$this->innerSelectionMock = Mockery::mock(Selection::class);

		$this->abstractBaseModelMock->shouldReceive('findAll')
			->andReturn($this->innerSelectionMock)
		;
	}

	public function tearDown()
	{
		Mockery::close();
	}

	public function testExplodeLikeNameOne()
	{
		$this->innerSelectionMock->shouldReceive('where')
			->withArgs([
				'(name LIKE ?)',
				'%hp%'
			])
			->once()
			->andReturn($this->returnSelectionMock)
		;

		$result = $this->abstractBaseModelMock->explodeLike('name', 'hp');
		Assert::type(Selection::class, $result);
	}

	public function testExplodeLikeName()
	{
		$this->innerSelectionMock->shouldReceive('where')
			->withArgs([
				'(name LIKE ?) AND (name LIKE ?) AND (name LIKE ?)',
				['%hp%', '%1200%', '%ce%']
			])
			->once()
			->andReturn($this->returnSelectionMock)
		;

		$result = $this->abstractBaseModelMock->explodeLike('name', 'hp 1200 ce');
		Assert::type(Selection::class, $result);
	}

	public function testExplodeLikeNameSkuOne()
	{
		$this->innerSelectionMock->shouldReceive('where')
			->withArgs([
				'((name LIKE ?)) OR ((sku LIKE ?))',
				['%hp%', '%hp%']
			])
			->once()
			->andReturn($this->returnSelectionMock)
		;

		$result = $this->abstractBaseModelMock->explodeLike(['name', 'sku'], 'hp');
		Assert::type(Selection::class, $result);
	}

	public function testExplodeLikeNameSku()
	{
		$this->innerSelectionMock->shouldReceive('where')
			->withArgs([
				'((name LIKE ?) AND (name LIKE ?) AND (name LIKE ?)) OR ((sku LIKE ?) AND (sku LIKE ?) AND (sku LIKE ?))',
				[0 => '%hp%', 1 => '%1200%', 2 => '%ce%', 3 => '%hp%', 4 => '%1200%', 5 => '%ce%']
			])
			->once()
			->andReturn($this->returnSelectionMock)
		;

		$result = $this->abstractBaseModelMock->explodeLike(['name', 'sku'], 'hp 1200 ce');
		Assert::type(Selection::class, $result);
	}


}

(new AbstractBaseModel())->run();
