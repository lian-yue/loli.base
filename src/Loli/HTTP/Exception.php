<?php
/* ************************************************************************** */
/*
/*	Lian Yue
/*
/*	Url: www.lianyue.org
/*	Email: admin@lianyue.org
/*	Author: Moon
/*
/*	Created: UTC 2015-08-21 13:42:16
/*
/* ************************************************************************** */
/* ************************************************************************** */
/*
/*	Lian Yue
/*
/*	Url: www.lianyue.org
/*	Email: admin@lianyue.org
/*	Author: Moon
/*
/*	Created: UTC 2015-04-03 07:05:11
/*	Updated: UTC 2015-04-03 07:05:16
/*
/* ************************************************************************** */
namespace Loli\HTTP;
use Loli\LogException;
class Exception extends LogException{
	public function __construct($message, $code = 500, $level = 3, Exception $previous = NULL) {
		parent::__construct($message, $code ? $code : 500, $level, $previous);
	}
}