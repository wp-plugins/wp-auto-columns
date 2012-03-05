<?php

class HTMLSplitter
{

	protected $skip;
	protected $pass;
	protected $modifiers;
	protected $headers;
	protected $splittable;
	protected $break;

	/**
	 *
	 */
	public function __construct()
	{
		$this->skip = array('head');
		$this->pass = array('#document', 'html', 'body');
		$this->modifiers = array(
			'#default' => array('line-count' => 60, 'line-height' => 1, 'margin-bottom' => 0),
			'h1' => array('line-count' => 20, 'line-height' => 2.5, 'margin-bottom' => 1),
			'h2' => array('line-count' => 25, 'line-height' => 2, 'margin-bottom' => 1),
			'h3' => array('line-count' => 40, 'line-height' => 1.5, 'margin-bottom' => 1),
			'br' => array('margin-bottom' => 1),
			'hr' => array('margin-bottom' => 1),
			'ul' => array('margin-bottom' => 1),
			'ol' => array('margin-bottom' => 1),
			'p' => array('margin-bottom' => 1),
		);
		$this->headers = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
		$this->splittable = array('div', 'p', 'ul');
		$this->break = array('br');
	}

	/**
	 *
	 */
	public function setModifiers($modifiers)
	{
		$this->modifiers = array_merge($this->modifiers, $modifiers);
	}

	/**
	 *
	 */
	public function split($html, $columns = 2)
	{
		$doc = new DOMDocument();

		$doc->loadHTML('<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>');

		$nodeArray = $this->processNode($doc);

		// calculate
		$totalHeight = $this->calculateHeight($nodeArray, $this->modifiers['#default']);

		$colHeight = floor($totalHeight / $columns);

		//
		$cols = array();

		$column = 0;
		$height = 0;
		while (count($nodeArray) > 0)
		{
			$node = array_shift($nodeArray);

			if (in_array($node->name, $this->splittable))
			{
				$current_tag = $node->name;

				$children = array();
				foreach ($node->children as $childNode)
				{
					// skip break tags in the beginning of the column
					if (in_array($childNode->name, $this->break) && count($children) == 0)
						continue;

					// add text representation of the child node
					$children[] = $childNode->node->C14N(false);
					$height += $childNode->height;

					// check we have to start new column
					if ($height >= $colHeight)
					{
						// construct node
						$nodeText = $this->constructStartTag($node) . implode('', $children) . '</' . $current_tag . '>';
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
				}

				// if we have untouched children
				if (count($children) > 0)
				{
					// construct node
					$nodeText = $this->constructStartTag($node) . implode('', $children) . '</' . $current_tag . '>';
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
				$height += $node->height;

				$header = in_array($node->name, $this->headers);
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
	protected function constructStartTag($node)
	{
		$ret = '<';
		$ret .= $node->name;

		if ($node->node->hasAttributes())
		{
			$attributes = array();

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
	 *
	 * @param type $node
	 * @return null|\stdClass
	 */
	protected function processNode($node)
	{
		$name = $node->nodeName;

		if (in_array($name, $this->skip))
		{
			return null;
		}
		else if (in_array($name, $this->pass))
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
	 *
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
	 *
	 * @param type $nodeArray
	 * @param type $params
	 * @return type
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

			//
			if (count($node->children) > 0)
			{
				$node->height += $this->calculateHeight($node->children, $nodeParams);
			}
			else if ($node->name == '#text')
			{
				$lines = ceil(strlen($node->text) / $params['line-count']);

				$node->height += ($lines * $params['line-height']) + $params['margin-bottom'];
			}

			$height += $node->height;
		}

		return $height;
	}

}