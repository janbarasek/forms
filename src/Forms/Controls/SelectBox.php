<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;


/**
 * Select box control that allows single item selection.
 */
class SelectBox extends ChoiceControl
{
	/** validation rule */
	public const Valid = ':selectBoxValid';
	public const VALID = self::Valid;

	/** of option / optgroup */
	private array $options = [];
	private string|object|false $prompt = false;
	private array $optionAttributes = [];


	public function __construct($label = null, ?array $items = null)
	{
		parent::__construct($label, $items);
		$this->setOption('type', 'select');
		$this->addCondition(
			fn() => $this->prompt === false
			&& $this->options
			&& $this->control->size < 2,
		)->addRule(Nette\Forms\Form::Filled, Nette\Forms\Validator::$messages[self::Valid]);
	}


	/**
	 * Sets first prompt item in select box.
	 * @param  string|object|false  $prompt
	 */
	public function setPrompt($prompt): static
	{
		$this->prompt = $prompt;
		return $this;
	}


	/**
	 * Returns first prompt item?
	 * @return string|object|false
	 */
	public function getPrompt()
	{
		return $this->prompt;
	}


	/**
	 * Sets options and option groups from which to choose.
	 * @return static
	 */
	public function setItems(array $items, bool $useKeys = true)
	{
		if (!$useKeys) {
			$res = [];
			foreach ($items as $key => $value) {
				unset($items[$key]);
				if (is_array($value)) {
					foreach ($value as $val) {
						$res[$key][(string) $val] = $val;
					}
				} else {
					$res[(string) $value] = $value;
				}
			}

			$items = $res;
		}

		$this->options = $items;
		return parent::setItems(Nette\Utils\Arrays::flatten($items, preserveKeys: true));
	}


	public function getControl(): Nette\Utils\Html
	{
		$items = $this->prompt === false ? [] : ['' => $this->translate($this->prompt)];
		foreach ($this->options as $key => $value) {
			$items[is_array($value) ? $this->translate($key) : $key] = $this->translate($value);
		}

		return Nette\Forms\Helpers::createSelectBox(
			$items,
			[
				'disabled:' => is_array($this->disabled) ? $this->disabled : null,
			] + $this->optionAttributes,
			$this->value,
		)->addAttributes(parent::getControl()->attrs);
	}


	public function addOptionAttributes(array $attributes): static
	{
		$this->optionAttributes = $attributes + $this->optionAttributes;
		return $this;
	}


	public function setOptionAttribute(string $name, mixed $value = true): static
	{
		$this->optionAttributes[$name] = $value;
		return $this;
	}


	public function isOk(): bool
	{
		return $this->isDisabled()
			|| $this->prompt !== false
			|| $this->getValue() !== null
			|| !$this->options
			|| $this->control->size > 1;
	}


	public function getOptionAttributes(): array
	{
		return $this->optionAttributes;
	}
}
