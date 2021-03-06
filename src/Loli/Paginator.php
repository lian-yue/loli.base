<?php
/* ************************************************************************** */
/*
/*	Lian Yue
/*
/*	Url: www.lianyue.org
/*	Email: admin@lianyue.org
/*	Author: Moon
/*
/*	Created: UTC 2016-01-27 05:40:11
/*
/* ************************************************************************** */
namespace Loli;
use JsonSerializable;
use Psr\Http\Message\UriInterface;


use GuzzleHttp\Psr7\Uri as Psr7Uri;

class Paginator implements JsonSerializable{
	protected $uri;

	protected $key = 'page';

	protected $current = 1;

	protected $limit = 20;

	protected $total = 0;

	protected $max = 0;

	protected $for = 3;

	protected $ellipsis = true;

	public function __construct($uri = null, $current = false, $limit = 20) {
		if ($current === false) {
			if (($params = Route::request()->getAttribute('params')) && isset($params[$this->key])) {
				$current = $params[$this->key];
			} elseif (($parsedBody = Route::request()->getParsedBody()) && is_array($parsedBody) && isset($parsedBody[$this->key])) {
				$current = $parsedBody[$this->key];
			} elseif (($queryParams = Route::request()->getQueryParams()) && is_array($queryParams) && isset($queryParams[$this->key])) {
				$current = $queryParams[$this->key];
			} else {
				$current = 1;
			}
		}
        $this->__set('uri', $uri ? $uri : Route::request()->getUri());
        $this->__set('current', $current);
        $this->__set('limit', $limit);
	}

	public function __get($name) {
		switch ($name) {
			case 'start':
				return 1;
				break;
			case 'end':
				$end = ceil($this->total / $this->limit);
                if ($end < 1) {
                    $end = 1;
                }
				if ($this->max) {
					$end = $this->max;
				}
				return $end;
				break;
			case 'prev':
				if ($this->current > 1) {
					return $this->current - 1;
				}
				return false;
				break;
			case 'next':
				if ($this->end > $this->current) {
					return 1 + $this->current;
				}
				return false;
				break;
			case 'items':
				$current = $this->current;
				$min = max(1, $current - $this->for);
				$max = min($current + $this->for, $this->end);
				$items = [];

				$items[] = ['type' => 'prev', 'value' => self::translate('&laquo; Previous'), 'uri' => ($prev = $this->prev) ? $this->uri($prev) : false];

				if ($this->ellipsis && $min > 1) {
					$items[] = ['type' => 'ellipsis', 'value' => self::translate('...')];
				}

                for ($i = $min; $i <= $max; $i++) {
					$items[] = ['type' => 'uri', 'value' => $i, 'uri' => $this->uri($i), 'active' => $i === $current];
				}

				if ($this->ellipsis && $max < $this->end) {
					$items[] = ['type' => 'ellipsis', 'value' => self::translate('...')];
				}
				$items[] = ['type' => 'next', 'value' => self::translate('Next &raquo;'), 'uri' => ($next = $this->next) ? $this->uri($next) : false];

				return $items;
				break;
            case 'offset':
                return ($this->current - 1) * $this->limit;
                break;
			default:
				if (isset($this->$name)) {
					return $this->$name;
				}
		}
		return null;
	}


	public function __set($name, $value) {
		switch ($name) {
			case 'uri':
				if (!$value instanceof UriInterface) {
					$value = new Psr7Uri($value);
				}
				$this->uri = $value;
				break;
			case 'key':
				$this->key = to_string($value);
				break;
			case 'limit':
				if ($value < 1) {
					$value = 1;
				}
				$this->limit = (int) $value;
				break;
			case 'current':
				if ($value < 1) {
					$value = 1;
				}
				if ($this->max && $value > $this->max) {
					$value = $this->max;
				}
				$this->current = (int) $value;
				break;
			case 'total':
				if ($value < 0) {
					$value = 0;
				}
				$this->total = (int) $value;
				break;
			case 'ellipsis':
				$this->ellipsis = (int) $value;
				break;
			case 'max':
				$this->__set('max', $this->current);
				$this->max = (int) $value;
			default:
				throw new \Exception(__METHOD__. '('. $name .') Paginator set name');
		}
	}


	public function uri($page) {
        if ($page === 1) {
            $page = null;
        }
		if ($this->uri instanceof Uri) {
			return $this->uri->withQueryParam($this->key, $page);
		}
		if ($query = $this->uri->getQuery()) {
			parse_str($query, $queryParams);
		} else {
			$queryParams = [];
		}
		$queryParams[$this->key] = $page;
		return $this->uri->withQuery(http_build_query($queryParams, null, '&'));
	}


	public function jsonSerialize() {
		$array = [];
		foreach (['start', 'end', 'prev', 'next', 'uri', 'key', 'current', 'limit','total', 'items'] as $name) {
			$array[$name] = $this->__get($name);
		}
		$array['uri'] = $this->uri('{page}');
		$array['items'] = $this->items;
		return $array;
	}

	public function __toString() {
		$results = '<ul class="pagination">';
		foreach($this->items as $item) {
			if (empty($item['uri'])) {
				$results .= '<li class="disabled '. $item['type'] .'"><span>'. $item['value'] .'</span></li>';
			} else {
				$class = $item['type'];
				if (!empty($item['active'])) {
					$class .= ' active';
				}
				$results .= '<li class="'. $class .'"><a href="'. $item['uri'] .'" ' . (in_array($item['type'], ['prev', 'next'], true) ? 'rel="'. $item['type'] .'"' : '') . '>'. $item['value'] .'</span></li>';
			}
		}
		$results .='</ul>';
	}

	public function __call($name, $args) {
		switch (substr($name, 0, 3)) {
			case 'get':
				if ($this->__isset($name = snake(substr($name, 3)))) {
					return $this->__get($name);
				}
				break;
			case 'add':
				if (!$this->__isset($name = snake(substr($name, 3)))) {
					$this->__set($name, $args ? $args[0] : null);
				}
				return $this;
				break;
			case 'set':
				$this->__set(snake(substr($name, 3)), $args ? $args[0] : null);
				return $this;
				break;
			default:
				if (($value = $this->__get($name)) && ($value instanceof Closure || (is_object($value) && method_exists($value, '__invoke')))) {
					return $value(...$args);
				}
		}
		throw new \Exception(__METHOD__. '('. $name .') Method does not exist');
	}

	public static function translate($text, $original = true) {
		return Locale::translate($text, ['paginator', 'default'], $original);
	}
}
