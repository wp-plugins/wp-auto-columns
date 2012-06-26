<?php

/**
 *
 * @todo check attributes of IMG tag
 *
 */
class HTMLSplitter
{

	// split configuration
	protected $config;

	/**
	 *
	 */
	public function __construct($config = null)
	{
		// default configuration values
		$this->config = array(
			// line height in pixels
			'line_height' => 14,
			// skip tags
			'skip' => array('head'),
			// pass tags as is
			'pass' => array('#document', 'html', 'body'),
			// header tags
			'headers' => array(),
			// splittable tags
			'splittable' => array(),
			// inline tags
			'inline' => array('span', 'a', 'b', 'strong', 'i', 'em'),
			// line break tags
			'break' => array('br'),
			// size modifiers
			'modifiers' => array(
				'default' => array('line-chars' => 50, 'line-height' => 1, 'margin-bottom' => 0),
				'h1' => array('line-chars' => 20, 'line-height' => 2.5, 'margin-bottom' => 1),
				'h2' => array('line-chars' => 25, 'line-height' => 2, 'margin-bottom' => 1),
				'h3' => array('line-chars' => 40, 'line-height' => 1.5, 'margin-bottom' => 1),
				'br' => array('line-height' => 0.5, 'margin-bottom' => 1),
				'hr' => array('margin-bottom' => 1),
				'ul' => array('margin-bottom' => 1),
				'ol' => array('margin-bottom' => 1),
				'p' => array('margin-bottom' => 1),
			)
		);

		if ($config != null)
		{
			$this->setConfig($config);
		}
	}

	/**
	 * Set external modifiers
	 */
	public function setConfig($config)
	{
		$this->config = $this->array_merge($this->config, $config);
	}

	/**
	 *
	 * @param type $array1
	 * @param type $array2
	 * @return
	 */
	private function array_merge()
	{
		$arrays = func_get_args();
		$merged = array();
		while ($arrays)
		{
			$array = array_shift($arrays);
			if (!$array)
				continue;

			foreach ($array as $key => $value)
			{
				if (is_string($key))
					if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
						$merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
					else
						$merged[$key] = $value;
				else
					$merged[] = $value;
			}
		}
		return $merged;
	}

	/**
	 * Split document into columns
	 *
	 * @param $html
	 * @param $columns
	 * @return array of columns
	 */
	public function split($html, $columns = 2)
	{
		// repair html and fix tags
		$tidy = new tidy();

		// tidy config
		$config = array(
			'indent' => false,
			'output-xhtml' => true
		);
		$tidy->parseString($html, $config, 'UTF8');

		// clean and repair source html
		$tidy->cleanRepair();

		// parse html
		$doc = new DOMDocument();
		$doc->loadHTML('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>' . $tidy->body() . '</html>');

		// process html
		$nodeArray = $this->processNode($doc);

		// calculate estimated height of whole html
		$totalHeight = $this->calculateHeight($nodeArray, $this->config['modifiers']['default']);

		// calculate column height
		$colHeight = floor($totalHeight / $columns);

		// array of columns
		$cols = array();

		$column = 0;
		$height = 0;
		while (count($nodeArray) > 0)
		{
			$node = array_shift($nodeArray);

			if (in_array($node->name, $this->config['splittable']))
			{
				$current_tag = $node->name;

				$item = 1;
				$start = 1;
				$children = array();
				foreach ($node->children as $childNode)
				{
					// skip break tags in the beginning of the column
					if (in_array($childNode->name, $this->config['break']) && count($children) == 0)
						continue;

					// add text representation of the child node
					$children[] = $childNode->node->C14N(false);
					$height += $childNode->height;

					// check we have to start new column
					if ($height >= $colHeight)
					{
						// construct node
						$nodeText = $this->constructStartTag($node, $start) . implode('', $children) . '</' . $current_tag . '>';
						$start = $item + 1;
						$cols[$column][] = $nodeText;
						$children = array();

						// next column
						if ($column < $columns - 1)
						{
							$column++;
							$cols[$column] = array();
							$height = 0;
						}
					}

					if ($childNode->name == 'li')
					{
						$item++;
					}
				}

				// if we have untouched children
				if (count($children) > 0)
				{
					// construct node
					$nodeText = $this->constructStartTag($node, $start) . implode('', $children) . '</' . $current_tag . '>';
					$cols[$column][] = $nodeText;

					if ($height >= $colHeight && $column < $columns - 1)
					{
						// next column
						$column++;
						$cols[$column] = array();
						$height = 0;
					}
				}
			}
			else
			{
				$cols[$column][] = $node->node->C14N(false);
				//$cols[$column][] = '<p><strong>' . $node->height . '</strong></p>';
				$height += $node->height;

				$header = in_array($node->name, $this->config['headers']);
				if (!$header && $height >= $colHeight)
				{
					// next column
					$column++;
					$cols[$column] = array();
					$height = 0;
				}
			}
		}

		// fill last columns if can
		if (count($cols[$columns - 1]) == 0 && count($cols[$columns - 2]) > 1)
		{
			array_push($cols[$columns - 1], array_pop($cols[$columns - 2]));
		}

		return $cols;
	}

	/**
	 * Construct html opening tag for splitted nodes
	 *
	 * @param type $node
	 * @return string
	 */
	protected function constructStartTag($node, $start = '')
	{
		$ret = '<';
		$ret .= $node->name;

		if ($node->node->hasAttributes() || $node->name == 'ol')
		{
			$attributes = array();

			if ($node->name == 'ol')
			{
				// respect OL numbers
				$attributes[] = 'start="' . $start . '"';
			}

			$length = $node->node->attributes->length;
			for ($i = 0; $i < $length; $i++)
			{
				$attr = $node->node->attributes->item($i);
				$attributes[] = $attr->name . '="' . $attr->value . '"';
			}

			if (count($attributes) > 0)
			{
				$ret .= ' ' . implode(' ', $attributes);
			}
		}

		$ret .= '>';

		return $ret;
	}

	/**
	 * Preprocess one node
	 *
	 * @param DOMNode $node
	 * @return null|\stdClass
	 */
	protected function processNode($node)
	{
		$name = $node->nodeName;

		if (in_array($name, $this->config['skip']))
		{
			return null;
		}
		else if (in_array($name, $this->config['pass']))
		{
			return $this->getChildArray($node);
		}

		$n = new stdClass();
		$n->name = $name;
		$n->height = 0;
		$n->node = $node;

		if ($name == '#text')
		{
			// check text nodes
			$text = trim($node->textContent);
			if ($text == '')
			{
				// skip empty text nodes
				return null;
			}
			$n->text = $text;
		}

		// process child nodes if any
		if ($node->hasChildNodes())
		{
			$n->children = $this->getChildArray($node);
		}

		return $n;
	}

	/**
	 * Get array of child nodes
	 *
	 * @param DOMNode $node
	 * @return array
	 */
	protected function getChildArray($node)
	{
		$ret = array();

		if ($node->hasChildNodes())
		{
			// convert to array of child nodes
			foreach ($node->childNodes as $childNode)
			{
				$c = $this->processNode($childNode);

				if (is_array($c))
				{
					$ret = array_merge($ret, $c);
				}
				else if ($c != null)
				{
					$ret[] = $c;
				}
			}
		}

		return $ret;
	}

	/**
	 * Calculate estimated height of node with subnodes
	 *
	 * @param array $nodeArray
	 * @param array $params
	 * @return integer height value
	 */
	protected function calculateHeight($nodeArray, $params)
	{
		$height = 0;
		foreach ($nodeArray as $node)
		{
			$name = $node->name;

			// update params
			$modifier = $this->modifiers[$name];
			if ($modifier)
			{
				$nodeParams = array_merge($params, $modifier);
			}
			else
			{
				$nodeParams = $params;
			}

			// calculate node height
			if (count($node->children) > 0)
			{
				// node has children
				$children_height = $this->calculateHeight($node->children, $nodeParams);

				$node->height += $children_height + ($nodeParams['margin-bottom'] * $this->config['line_height'] * $nodeParams['line-height']);
			}
			else if ($node->name == 'img')
			{
				// image tag, try to calculate height based on attribute
				$img_height = intval($node->node->getAttribute('height'));
				if ($img_height != 0)
				{
					$node->height += $img_height;
				}
				else
				{
					// no height attribute, try to process the image
					$img_src = $node->node->getAttribute('src');
				}
			}
			else if ($node->name == '#text')
			{
				// text node, height depends on number of chars
				$lines = ceil(strlen($node->text) / $nodeParams['line-chars']);

				// estimate text height
				$node->height += ($lines * $this->config['line_height'] * $nodeParams['line-height']);
			}
			else
			{
				// other nodes, height is calculated by estimated measurements
				$node->height += (1 * $this->config['line_height'] * $nodeParams['line-height'])
					+ ($nodeParams['margin-bottom'] * $this->config['line_height'] * $nodeParams['line-height']);
			}

			// update node height
			$height += $node->height;
		}

		return $height;
	}

}