<?php
/**
 * @project    Hesper Framework
 * @author     Alex Gorbylev
 * @originally onPHP Framework
 * @originator Konstantin V. Arkhipov
 */
namespace Hesper\Core\Form\Primitive;

/**
 * Class PrimitiveAlias
 * @package Hesper\Core\Form\Primitive
 */
final class PrimitiveAlias extends BasePrimitive {

	private $primitive = null;

	public function __construct($name, BasePrimitive $prm) {
		$this->name = $name;
		$this->primitive = $prm;
	}

	public function getInner() {
		return $this->primitive;
	}

	public function getName() {
		return $this->name;
	}

	public function getDefault() {
		return $this->primitive->getDefault();
	}

	/**
	 * @return PrimitiveAlias
	 **/
	public function setDefault($default) {
		$this->primitive->setDefault($default);

		return $this;
	}

	public function getValue() {
		return $this->primitive->getValue();
	}

	public function getRawValue() {
		return $this->primitive->getRawValue();
	}

	public function getValueOrDefault() {
		return $this->primitive->getValueOrDefault();
	}

	/**
	 * @deprecated by getFormValue
	 * since version 1.0 by getValueOrDefault
	 **/
	public function getActualValue() {
		return $this->primitive->getActualValue();
	}

	public function getSafeValue() {
		return $this->primitive->getSafeValue();
	}

	public function getFormValue() {
		if (!$this->primitive->isImported()) {
			if ($this->primitive->getValue() === null) {
				return null;
			}

			return $this->primitive->exportValue();
		}

		return $this->primitive->getRawValue();
	}

	/**
	 * @return PrimitiveAlias
	 **/
	public function setValue($value) {
		$this->primitive->setValue($value);

		return $this;
	}

	/**
	 * @return PrimitiveAlias
	 **/
	public function dropValue() {
		$this->primitive->dropValue();

		return $this;
	}

	/**
	 * @return PrimitiveAlias
	 **/
	public function setRawValue($raw) {
		$this->primitive->setRawValue($raw);

		return $this;
	}

	public function isImported() {
		return $this->primitive->isImported();
	}

	/**
	 * @return PrimitiveAlias
	 **/
	public function clean() {
		$this->primitive->clean();

		return $this;
	}

	public function importValue($value) {
		return $this->primitive->importValue($value);
	}

	public function exportValue() {
		return $this->primitive->exportValue();
	}

	public function getCustomError() {
		return $this->primitive->getCustomError();
	}

	public function import($scope) {
		if (array_key_exists($this->name, $scope)) {
			return $this->primitive->import([$this->primitive->getName() => $scope[$this->name]]);
		}

		return null;
	}
}
