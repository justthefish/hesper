<?php
/**
 * @project    Hesper Framework
 * @author     Alex Gorbylev
 * @originally onPHP Framework
 * @originator Konstantin V. Arkhipov
 */
namespace Hesper\Core\DB;

use Hesper\Core\Base\Assert;
use Hesper\Core\OSQL\DataType;
use Hesper\Core\OSQL\DBColumn;
use Hesper\Core\OSQL\DialectString;
use Hesper\Core\Exception\WrongArgumentException;

/**
 * PostgreSQL dialect.
 * @package Hesper\Core\DB
 * @see     http://www.postgresql.org/
 */
final class PostgresDialect extends Dialect {

	private static $tsConfiguration = 'utf8_russian';
	private static $rankFunction    = 'rank';

	public static function getTsConfiguration() {
		return self::$tsConfiguration;
	}

	public static function setTsConfiguration($configuration) {
		self::$tsConfiguration = $configuration;
	}

	public static function setRankFunction($rank) {
		self::$rankFunction = $rank;
	}

	public function quoteValue($value) {
		return "'" . pg_escape_string($this->getLink(), $value) . "'";
	}

	public static function toCasted($field, $type) {
		return "{$field}::{$type}";
	}

	public function prepareFullText(array $words, $logic) {
		$glue = ($logic == DB::FULL_TEXT_AND) ? ' & ' : ' | ';

		return mb_strtolower(implode($glue, array_map([$this, 'quoteValue'], $words)));
	}

	public function quoteBinary($data) {
		$esc = pg_escape_bytea($this->getLink(), $data);
		if (mb_strpos($esc, '\\x') === 0) {
			// http://www.postgresql.org/docs/9.1/static/datatype-binary.html
			// if pg_escape_bytea use postgres 9.1+ it's return value like '\x00aabb' (new bytea hex format),
			// but must return '\\x00aabb'. So we use this fix:'
			return "E'\\" . $esc . "'";
		} else {
			//if function escape value like '\\000\\123' - all ok
			return "E'" . $esc . "'";
		}
	}

	public function unquoteBinary($data) {
		return pg_unescape_bytea($data);
	}

	public function typeToString(DataType $type) {
		if ($type->getId() == DataType::BINARY) {
			return 'BYTEA';
		}

		if (defined('POSTGRES_IP4_ENABLED')) {

			if ($type->getId() == DataType::IP) {
				return 'ip4';
			}

			if ($type->getId() == DataType::IP_RANGE) {
				return 'ip4r';
			}
		}

		return parent::typeToString($type);
	}

	public function hasTruncate() {
		return true;
	}

	public function hasMultipleTruncate() {
		return true;
	}

	public function hasReturning() {
		return true;
	}

	public function fullTextSearch($field, $words, $logic) {
		$searchString = $this->prepareFullText($words, $logic);
		$field = $this->fieldToString($field);

		return "({$field} @@ to_tsquery('" . self::$tsConfiguration . "', " . self::quoteValue($searchString) . "))";
	}

	public function fullTextRank($field, $words, $logic) {
		$searchString = $this->prepareFullText($words, $logic);
		$field = $this->fieldToString($field);

		return self::$rankFunction . "({$field}, to_tsquery('" . self::$tsConfiguration . "', " . self::quoteValue($searchString) . "))";
	}

	public function preAutoincrement(DBColumn $column) {
		self::checkColumn($column);

		return 'CREATE SEQUENCE "' . $this->makeSequenceName($column) . '";';
	}

	public function postAutoincrement(DBColumn $column) {
		self::checkColumn($column);

		return 'default nextval(\'' . $this->makeSequenceName($column) . '\')';
	}

	public function quoteIpInRange($range, $ip) {
		$string = '';

		if ($ip instanceof DialectString) {
			$string .= $ip->toDialectString($this);
		} else {
			$string .= $this->quoteValue($ip);
		}

		$string .= ' <<= ';

		if ($range instanceof DialectString) {
			$string .= $range->toDialectString($this);
		} else {
			$string .= $this->quoteValue($range);
		}

		return $string;
	}

	public function quoteArray($values, $type)
	{
		if (empty($values)) {
			return self::LITERAL_NULL;
		}
		// add qoutes
		foreach ($values as &$item) {
			if ($type === DataType::INTEGER) {
				$item = intval($item);
			} else if ($type === DataType::REAL) {
				$item = doubleval($item);
			} elseif ($type === DataType::VARCHAR) {
				$item = $this->quoteValue($item);
			} else {
				throw new WrongArgumentException('unknown type of array!');
			}
		}
		return 'ARRAY['.implode(', ',$values).']';
	}


	public function quoteJson($values, $type) {
		if( empty($values) ) {
			return self::LITERAL_NULL;
		}
		$json = str_replace('\\u0000', '', json_encode($values));
		return $this->quoteValue($json);
	}

	protected function makeSequenceName(DBColumn $column) {
		return $column->getTable()
		              ->getName() . '_' . $column->getName();
	}

	private static function checkColumn(DBColumn $column) {
		Assert::isTrue(($column->getTable() !== null) && ($column->getDefault() === null));
	}
}
