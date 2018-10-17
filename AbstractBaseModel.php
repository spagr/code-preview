<?php declare(strict_types=1);

namespace App\Modules\Core\Model;

use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;
use Nette\Utils\Strings;

abstract class AbstractBaseModel
{
    /**
     * @var string
     */
    protected const TABLE_NAME = '';

    /**
     * @var Context
     */
    protected $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

	/**
	 * @return Selection
	 */
    public function findAll(): Selection
    {
        return $this->database->table($this::TABLE_NAME);
    }

	/**
	 * @param string $column
	 * @param mixed $value
	 * @param array|null $addWhere
	 * @return Selection
	 */
	public function findBy($column, $value, $addWhere = null, $detectLikeOperator = false)
	{
		if (
			$detectLikeOperator
			&& (!is_array($value))
			&& (Strings::contains($value, '_') || Strings::contains($value, '%'))
			&& (Strings::match($column, '~^\w+$~') !== null) // hledame jen jedno slovo = jmeno sloupce
		) {
			$column = $column . ' LIKE';
		}
		$selection = $this->findAll()->where($column, $value);
		if ($addWhere !== null) {
			$selection->where($addWhere);
		}
		return $selection;
	}

	/**
	 * Explode query by words and make WHERE $column LIKE %word% batch
	 * @param string|array $column
	 * @param string $query
	 * @return Selection
	 */
	public function explodeLike($columns, $query) : Selection
	{
		$selection = $this->findAll();
		preg_match_all("~[\\pL\\pN_]+('[\\pL\\pN_]+)*~u", stripslashes($query), $matches);
		if (!is_array($columns)) {
			$columns = (array)$columns;
		}
		$resultString = [];
		$resultValues = [];
		foreach ($columns as $column) {
			$columnConditions = [];
			$columnValues = [];
			foreach ($matches[0] as $part) {
				//$columnCondition[] = [$column . ' LIKE' => '%' . $part . '%',];
				$columnConditions[] = $column . ' LIKE ?';
				$columnValues[] = '%'.$part.'%';
			}
			if (count($columnConditions) > 0) {
				$columnString = '(' . implode(') AND (', $columnConditions) . ')';
			} else {
				$columnString = '(' . current($columnConditions) . ')';
			}
			$resultString[] = $columnString;
			$resultValues = array_merge($resultValues, $columnValues);
		}

		if (count($resultString) === 1) {
			return $selection->where(
				current($resultString),
				count($resultValues) === 1 ? current($resultValues) : $resultValues
			);
		}
		$orString = '(' . implode(') OR (', $resultString) . ')';
		return $selection->where($orString, $resultValues);
	}


	/**
	 * @param mixed $primaryKey
	 * @return false|ActiveRow
	 */
	public function get($primaryKey)
	{
		return $this->findAll()->get($primaryKey);
	}


    /**
     * Inserts row in a table.
     * @param  mixed[]|\Traversable|Selection array($column => $value)|\Traversable|Selection for INSERT ... SELECT
     * @return ActiveRow|IRow|int|bool Returns IRow or number of affected rows for Selection or table without primary key
     */
    public function insert(iterable $data)
    {
        return $this->findAll()
            ->insert($data);
    }

    /**
     * Updates all rows in result set.
     * Joins in UPDATE are supported only in MySQL.
     * @param mixed[] ($column => $value)
     * @return int number of affected rows
     */
    public function update(iterable $where, iterable $data): int
    {
        return $this->findAll()
            ->where($where)
            ->update($data);
    }

    /**
     * @param mixed[] $data
     */
    public function delete(array $data): void
    {
        $this->findAll()->where($data)->delete();
    }
}
