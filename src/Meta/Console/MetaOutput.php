<?php
/**
 * @project    Hesper Framework
 * @author     Alex Gorbylev
 * @originally onPHP Framework
 * @originator Konstantin V. Arkhipov
 */
namespace Hesper\Meta\Console;

final class MetaOutput {

	private $out = null;

	public function __construct(TextOutput $out) {
		$this->out = $out;
	}

	/**
	 * @return TextOutput
	 **/
	public function getOutput() {
		return $this->out;
	}

	/**
	 * @return MetaOutput
	 **/
	public function newLine() {
		$this->out->newLine();

		return $this;
	}

	/**
	 * @return MetaOutput
	 **/
	public function log($text, $bold = false) {
		return $this->defaultText($text, ConsoleMode::FG_WHITE, $bold);
	}

	/**
	 * @return MetaOutput
	 **/
	public function logLine($text, $bold = false) {
		return $this->defaultTextLine($text, ConsoleMode::FG_WHITE, $bold);
	}

	/**
	 * @return MetaOutput
	 **/
	public function info($text, $bold = false) {
		return $this->defaultText($text, ConsoleMode::FG_GREEN, $bold);
	}

	/**
	 * @return MetaOutput
	 **/
	public function infoLine($text, $bold = false) {
		return $this->defaultTextLine($text, ConsoleMode::FG_GREEN, $bold);
	}

	/**
	 * @return MetaOutput
	 **/
	public function warning($text) {
		return $this->defaultText($text, ConsoleMode::FG_BROWN, true);
	}

	/**
	 * @return MetaOutput
	 **/
	public function warningLine($text) {
		return $this->defaultTextLine($text, ConsoleMode::FG_BROWN, true);
	}

	/**
	 * @return MetaOutput
	 **/
	public function error($text, $bold = false) {
		return $this->errorText($text, ConsoleMode::FG_RED, $bold);
	}

	/**
	 * @return MetaOutput
	 **/
	public function errorLine($text, $bold = false) {
		return $this->errorTextLine($text, ConsoleMode::FG_RED, $bold);
	}

	/**
	 * @return MetaOutput
	 **/
	public function remark($text) {
		return $this->defaultText($text, ConsoleMode::FG_BLUE, true);
	}

	/**
	 * @return MetaOutput
	 **/
	public function remarkLine($text) {
		return $this->defaultTextLine($text, ConsoleMode::FG_BLUE, true);
	}

	/**
	 * @return MetaOutput
	 **/
	private function defaultText($text, $color, $bold) {
		$this->out->setMode($bold ? ConsoleMode::ATTR_BOLD : ConsoleMode::ATTR_RESET_ALL, $color, ConsoleMode::BG_BLACK)
		          ->write($text);

		if ($this->out instanceof ColoredTextOutput) {
			$this->out->resetAll();
		}

		return $this;
	}

	/**
	 * @return MetaOutput
	 **/
	private function defaultTextLine($text, $color, $bold) {
		$this->out->setMode($bold ? ConsoleMode::ATTR_BOLD : ConsoleMode::ATTR_RESET_ALL, $color, ConsoleMode::BG_BLACK)
		          ->writeLine($text);

		if ($this->out instanceof ColoredTextOutput) {
			$this->out->resetAll();
		}

		return $this;
	}

	/**
	 * @return MetaOutput
	 **/
	private function errorText($text, $color, $bold) {
		if ($this->out instanceof ColoredTextOutput) {
			$text = $this->out->wrapString($text);
		}

		$this->out->writeErr($text);

		return $this;
	}

	/**
	 * @return MetaOutput
	 **/
	private function errorTextLine($text, $color, $bold) {
		if ($this->out instanceof ColoredTextOutput) {
			$text = $this->out->wrapString($text);
		}

		$this->out->writeErrLine($text);

		return $this;
	}
}