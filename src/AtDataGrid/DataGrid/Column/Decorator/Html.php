<?php

namespace AtDataGrid\DataGrid\Column\Decorator;

use Nette\Diagnostics\Debugger;
class Html extends AbstractDecorator
{
	/**
	 * Callback for escaping.
	 *
	 * @var string
	 */
	protected $escape = 'html_entity_decode';
	
	/**
	 * Encoding to use in escaping mechanisms; defaults to utf-8 (UTF-8)
	 * @var string
	 */
	protected $encoding = 'UTF-8';
	
	/**
	 * Sets the _escape() callback.
	 *
	 * @param mixed $spec The callback for _escape() to use.
	 * @return Literal
	 */
	public function setEscape($spec)
	{
		$this->escape = $spec;
		return $this;
	}
	
	/**
	 * Escapes a value for output in a view script.
	 *
	 * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
	 * {@link $_encoding} setting.
	 *
	 * @param mixed $var The output to escape.
	 * @return mixed The escaped value.
	 */
	public function escape($var)
	{
		foreach (array('htmlspecialchars_decode', 'html_entity_decode') as $value)
			$var = call_user_func($value, $var, ENT_COMPAT);
		return $var;
	}
	
	/**
	 * Set encoding to use with htmlentities() and htmlspecialchars()
	 *
	 * @param string $encoding
	 * @return Literal
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;
		return $this;
	}
	
	/**
	 * Return current escape encoding
	 *
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}
    
    /**
     * Render escaping the value
     */
    public function render($value)
    {
        return parent::render($this->escape($value));
    }
}