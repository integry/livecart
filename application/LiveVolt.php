<?php

class LiveVolt extends \Phalcon\Mvc\View\Engine\Volt
{
	public function getCompiler()
	{
		if (empty($this->_compiler))
		{
			$this->_compiler = new LiveVoltCompiler($this->getView());
			$this->_compiler->setOptions($this->getOptions());
			$this->_compiler->setDI($this->getDI());

			$this->_compiler->addFunction('config', function($resolvedArgs, $exprArgs) {
				return '$this->getDI()->get(\'config\')->get(' . $resolvedArgs . ')';
			});

		}

		return $this->_compiler;
	}
}

class LiveVoltCompiler extends \Phalcon\Mvc\View\Engine\Volt\Compiler
{
	protected function _compileSource($source, $something = null)
	{
		$source = str_replace('{{', '<' . '?php $ng = <<<NG' . "\n" . '\x7B\x7B', $source);
		$source = str_replace('}}', '\x7D\x7D' . "\n" . 'NG;' . "\n" . ' echo $ng; ?' . '>', $source);

		$source = str_replace('[[', '{{', $source);
		$source = str_replace(']]', '}}', $source);

		return parent::_compileSource($source, $something);
	}
}