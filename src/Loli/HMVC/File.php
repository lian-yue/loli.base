<?php
/* ************************************************************************** */
/*
/*	Lian Yue
/*
/*	Url: www.lianyue.org
/*	Email: admin@lianyue.org
/*	Author: Moon
/*
/*	Created: UTC 2015-02-07 16:58:27
/*	Updated: UTC 2015-02-09 15:49:26
/*
/* ************************************************************************** */
namespace Loli\HMVC;
use Loli\Request, Loli\Response;
class_exists('Loli\Request') || exit;


class File{
	private $_stream, $_fileSize, $_offset, $_length, $_request, $_response;

	// 直接发送文件地址 用 header  用了不限速  X-Accel-Redirect, X-LIGHTTPD-send-file, X-Sendfile
	public $header = false;

	// 每次缓冲区大小
	public $buffer = 2097152;

	// 限制速度
	public $speed = false;

	// 是否检测链接已断开
	public $status = true;

	// 绝对刷送
	public $flag = true;

	// 发送文件或资源 文件  大小
	public function __construct($stream, $fileSize, Request &$request, Response &$response, $status = true, $flag = true, $speed = false, $header = false) {
		$this->_stream = $stream;
		$this->_fileSize = $fileSize;
		$this->_request = &$request;
		$this->_response = &$response;
		$this->status = $status;
		$this->flag = $flag;
		$this->speed = $speed;
		$this->header = $header;


		// 用 header 发送文件的
		if ($this->header && is_string($this->_stream)) {
			$response->setStatus(200);
			return;
		}


		// 开启允许分段下载
		$response->setHeader('Accept-Ranges', 'bytes');


		// 需要分段的
		if (($status = $response->getCacheStatus()) == 206) {

			if (count($range = $request->getRanges()) != 1) {
				// 不支持多段
				$status = 416;
			} else {
				// 206 检查文件大小等
				extract(end($range));

				// 末尾偏移的
				$offset = $offset < 0 ? $fileSize + $offset : $offset;
				if ($offset < 0 || $offset > $fileSize) {
					// 偏移量过大
					$status = 416;
				} else {
					// 发送长度
					$length = $length === false ? $fileSize - $offset : $length;
					if (($offset + $length) > $fileSize) {
						// 长度过大
						$status = 416;
					}
				}
				$this->_offset = $offset;
				$this->_length = $length;
			}
		} elseif ($status == 200) {
			$this->_offset = 0;
			$this->_length = $fileSize;
		}

		// 写入状态码
		$response->setStatus($status);

		if ($status < 400) {
			// 发送文件长度头
			$response->setHeader('Content-Length', $this->_length);
			$status == 206 && $response->setHeader('Content-Range', 'bytes ' . $this->_offset. '-'. ($this->_offset + $this->_length - 1) . '/' . $fileSize);
		} else {
			// 写入错误码
			$response->addMessage($status);
		}
	}


	// 发送文件
	public function send() {
		// 已经用 header 发送了
		if ($this->header && is_string($this->_stream)) {
			return;
		}

		// 发送 0 长度的
		if (!$this->_length) {
			is_resource($this->_stream) && @fclose($this->_stream);
			return;
		}

		// 关闭缓冲区
		$this->flag && ob_end_clean();


		// 绝对刷送
		ob_implicit_flush($this->flag);


		// 关闭浏览器后动作
		ignore_user_abort(!$this->status);


		// 发送全部的
		if (!$this->_offset && $this->_length == $this->_fileSize) {
			// 发送文件
			if (function_exists('http_send_file') && is_string($this->_stream)) {
				$this->speed && http_speed(0.1, intval($this->speed/ 10) + 1);
				http_send_file($this->_stream);
				return;
			}

			// 发送资源
			if (function_exists('http_send_stream') && is_resource($this->_stream)) {
				$this->speed && http_speed(0.1, intval($this->speed/ 10) + 1);
				http_send_stream($this->_stream);
				return;
			}

			// 发送文件 不限速的
			if (is_string($this->_stream) && !$this->speed) {
				readfile($this->_stream);
				return;
			}
		}


		// 打开
		$stream = is_resource($this->_stream) ? $this->_stream : fopen($this->_stream, 'rb');

		// 每次缓冲区大小
		$buffer = $this->speed ? min(intval($this->speed / 20), $this->buffer) : $this->buffer;

		// 设置偏移
		fseek($stream, $this->_offset);

		// 循环发送
		$sendLength = 0;
		while (!feof($stream) && $sendLength < $this->_length) {
			$length = ($sendLength + $buffer) > $this->_length ? $this->_length - $sendLength : $buffer;
			echo @fread($stream, $length);
			if ($this->status && connection_status() !== CONNECTION_NORMAL) {
				break;
			}
			$sendLength += $length;
			$this->speed && usleep(50000);
		}

		// 关闭
		@fclose($stream);
	}


	// 发送文件
	public function __toString() {
		$this->send();
		return '';
	}
}