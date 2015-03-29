<?php
/* ************************************************************************** */
/*
/*	Lian Yue
/*
/*	Url: www.lianyue.org
/*	Email: admin@lianyue.org
/*	Author: Moon
/*
/*	Created: UTC 2015-03-28 02:20:01
/*	Updated: UTC 2015-03-28 08:54:31
/*
/* ************************************************************************** */
namespace Loli\HTML;
class Style{
	// 全部 style 属性
	protected $names = [
		'background' => '', 'background-attachment' => '', 'background-color' => '', 'background-image' => '', 'background-position' => '', 'background-repeat' => '', 'background-clip' => '', 'background-origin' => '', 'background-size' => '',
		'border' => '', 'border-bottom' => '', 'border-bottom-color' => '', 'border-bottom-style' => '', 'border-bottom-width' => '', 'border-color' => '', 'border-left' => '', 'border-left-color' => '', 'border-left-style' => '', 'border-left-width' => '', 'border-right' => '', 'border-right-color' => '', 'border-right-style' => '', 'border-right-width' => '', 'border-style' => '', 'border-top' => '', 'border-top-color' => '', 'border-top-style' => '', 'border-top-width' => '', 'border-width' => '', 'border-collapse' => '', 'border-spacing' => '', 'border-bottom-left-radius' => '', 'border-bottom-right-radius' => '', 'border-image' => '', 'border-image-outset' => '', 'border-image-repeat' => '', 'border-image-slice' => '', 'border-image-source' => '', 'border-image-width' => '', 'border-radius' => '', 'border-top-left-radius' => '', 'border-top-right-radius' => '',
		'box-shadow' => '',
		'outline' => '', 'outline-color' => '', 'outline-style' => '', 'outline-width' => '',
		'overflow' => '', 'overflow-x' => '', 'overflow-y' => '', 'overflow-style' => '',
		'opacity' => '',
		'height' => '', 'width' => '', 'max-height' => '', 'max-width' => '', 'min-height' => '', 'min-width' => '',
		'font' => '', 'font-family' => '', 'font-size' => '', 'font-style' => '', 'font-variant' => '', 'font-weight' => '', 'font-size-adjust' => '',
		'list-style' => '', 'list-style-image' => '', 'list-style-position' => '', 'list-style-type' => '',
		'letter-spacing' => '', 'line-height' => '', 'text-shadow' => '', 'text-overflow' => '', 'white-space' => '',
		'text-align' => '', 'text-indent' => '', 'text-transform' => '', 'text-decoration' => '',
		'margin' => '', 'margin-bottom' => '', 'margin-left' => '', 'margin-right' => '', 'margin-top' => '',
		'padding' => '', 'padding-bottom' => '', 'padding-left' => '', 'padding-right' => '', 'padding-top' => '',
		'position' => '', 'left' => '', 'top' => '', 'bottom' => '',
		'display' => '',
		'visibility' => '',
		'z-index' => '',
		'clear' => '',
		'cursor' => '',
		'float' => '',
		'color' => '',
		'vertical-align' => '',
		'white-profile' => '',
		'word-spacing' => '', 'word-wrap' => '',
		'caption-side' => '',
		'empty-cells' => '',
		'table-layout' => '',
		'counter-reset' => '',
		'scrollbar-face-color' => '',
		'scrollbar-track-color' => '',
		'scrollbar-arrow-color' => '',


		// css 3
		'animation' => '', 'animation-name' => '', 'animation-duration' => '', 'animation-timing-function' => '', 'animation-delay' => '', 'animation-iteration-count' => '', 'animation-direction' => '', 'animation-play-state' => '',
		'box-align' => '', 'box-direction' => '', 'box-flex' => '', 'box-flex-group' => '', 'box-lines' => '', 'box-ordinal-group' => '', 'box-orient' => '', 'box-pack' => '',
		'column-count' => '', 'column-fill' => '', 'column-gap' => '', 'column-rule' => '', 'column-rule-color' => '', 'column-rule-style' => '', 'column-rule-width' => '', 'column-span' => '', 'column-width' => '', 'columns' => '',
		'transform' => '', 'transform-origin' => '', 'transform-style' => '', 'perspective' => '', 'perspective-origin' => '', 'backface-visibility' => '',
		'transition' => '', 'transition-property' => '', 'transition-duration' => '', 'transition-timing-function' => '', 'transition-delay' => '',

		// media 的
		'grid' => '', 'scan' => '', 'resolution' => '', 'monochrome' => '', 'min-color-index' => '', 'max-color-index' => '', 'device-height' => '', 'device-width' => '',
	];

	// css 图片
	protected $stylesUrl = ['background', 'background-attachment', 'background-image'];


	// 允许的class前缀
	protected $prefix = '';

	public function __invoke() {
		return call_user_func_array([$this, 'contents'], func_get_args());
	}

	/**
	 * 匹配 style 内容
	 * @param  string $contents 内容
	 * @return string
	 */
	public function contents($contents) {

	}

	/**
	 * 匹配一行 style
	 * @param  string  $values  一行属性
	 * @param  boolean $isArray 是否返回array
	 * @return string or array
	 */
	public function values($values, $isArray = false) {
		$styles = [];
		foreach (explode(';', preg_replace('/\s+/is', ' ',preg_replace('/\/\*.*?(\*\/|$)|&quot;|&#039;|&lt;|&gt;|&|\\\\|"|\'|\>|</is', '', $values))) as $value) {
			// 没有 : 的
			if (count($value = explode(':', $value, 2)) !== 2) {
				continue;
			}
			// 没匹配到
			if (!$this->value($name = trim($value[0]), $value = trim($value[1]))) {
				continue;
			}
			$styles[] = $name . ': ' . $value . ';';
		}
		return $isArray ? $styles : implode(' ', $styles);
	}

	/**
	 * 匹配单个 style 属性
	 * @param  string  $name  属性名
	 * @param  string  $value 属性值
	 * @return boolean
	 */
	public function value($name, $value) {
		return false;
		if (!($name = strtolower(trim($name))) || !($value = trim($value))) {
			return false;
		}

		// 前缀
		if (substr($name, 0, 3) === '-o-') {
			$key = substr($name, 3);
		} elseif (substr($name, 0, 4) === '-ms-') {
			$key = substr($name, 4);
		} elseif (substr($name, 0, 5) === '-moz-') {
			$key = substr($name, 5);
		} elseif (substr($name, 0, 8) === '-webkit-') {
			$key = substr($name, 8);
		} else {
			$key = $name;
		}

		// 不允许的标签
		if (!isset($this->names[$key]) || $this->names[$key] === false) {
			return false;
		}


		// 值允许指定value
		if (is_array($this->names[$key])) {
			return in_array(strtolower($value), $this->names[$key]);
		}

		// 正则匹配
		$pattern = $this->names[$key] ? '/'. strtr($this->names[$key], ['/' => '\\/']) .'/i' : '/^(?:[0-9a-z |%#.,-]*(?:(?:rgb|hsl)a?\s*\([0-9,.% ]+\)'. (in_array($key, $this->stylesUrl) ? '|url\s*\(\s*(?:https?\:)?\/\/\w+\.\w+[0-9a-z.\/_-]+?\s*\)' : '') .')?[0-9a-z !|%#.,-]*)*$/i';
		return preg_match($pattern, $value);
	}

	/**
	 * 匹配 media 属性
	 * @param  string $media attribute Value
	 * @return string
	 */
	public function media($media) {
		if (!preg_match_all('/([0-9a-z,& ]*)(?:\((\s*[0-9a-z_-]+\s*\:[^;\(\)]+\s*)\))*/is', $media, $matchs)) {
			return 'all';
		}

		$result = '';
		foreach ($matchs[1] as $key => $type) {
			if (!$type && !$matchs[2][$key]) {
				continue;
			}
			$rule = $matchs[2][$key];
			if ($rule && !($rule = $this->values($rule))) {
				continue;
			}
			if ($type) {
				$result .= $type;
			}
			if ($rule) {
				$result .= ' ('. trim($rule, ';') .')';
			}
		}
		return $result ? $result : 'all';
	}




	public function getName($name) {
		return isset($this->names[$name]) ? $this->names[$name] : false;
	}

	public function getNames() {
		return $this->names;
	}

	public function addName($name, $value) {
		$this->names += [$name => $value];
		return $this;
	}

	public function addNames(array $names) {
		$this->names += $names;
		return $this;
	}

	public function setName($name, $value) {
		$this->names[$name] = $value;
		return $this;
	}

	public function setNames(array $names) {
		$this->names = $names + $this->names;
		return $this;
	}

	public function removeName($name) {
		unset($this->names[$name]);
		return $this;
	}

	public function removeNames(array $names) {
		foreach ($names as $key =>$name) {
			unset($this->names[$key], $this->names[$name]);
		}
		return $this;
	}
}