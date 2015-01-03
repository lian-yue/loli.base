<?php
/* ************************************************************************** */
/*
/*	Lian Yue
/*
/*	Url: www.lianyue.org
/*	Email: admin@lianyue.org
/*	Author: Moon
/*
/*	Created: UTC 2014-04-10 10:53:37
/*	Updated: UTC 2015-01-03 08:58:43
/*
/* ************************************************************************** */
namespace Model;
use Loli\Model;

class Exit_ extends Model{
	public function m($a, $original = true) {
		return $this->Lang($a, ['message', 'default'], $original);
	}
	public function e($a, $original = true) {
		return $this->Lang($a, ['error', 'default'], $original);
	}

	public function get($a = []) {
		if (r('msg')) {
			if (!$a || in_array(go('msg'), (array) $a)) {
				$a = ['msg'];
			}
		}
	}

	public function msg($a, $to = false, $arr = []) {
		http_no_cache();
		@ob_clean();
		$a = $a ? (array) $a : [false];
		$msg_code = reset($a);
		$msg_args = $a;
		unset($msg_args[key($a)]);
		$msg = $this->m($a);

		@header('Content-Msg-Code: ' . $msg_code);
		@header('Content-Msg: ' . $msg);


		if ($to === true || (!$to && !$this->Ajax->is)) {
			if (!empty($_SERVER["HTTP_REFERER"])) {
				$to = $_SERVER["HTTP_REFERER"];
			} else {
				$to = 'http' . (is_ssl() ? 's' : '') .'://'. $_SERVER['HTTP_HOST'];
			}
		}
		if ($to) {
			$parse = parse_url($to);
			$parse['scheme'] = empty($parse['scheme']) ? (is_ssl() ? 'https' : 'http') : $parse['scheme'];
			$parse['query'] = empty($parse['query']) ? [] : parse_string($parse['query']);
			$parse['query']['err'] = null;
			$parse['query']['err_name'] = null;
			$parse['query']['err_code'] = null;
			$parse['query']['err_args'] = null;
			$parse['query']['msg'] = $msg;
			$parse['query']['msg_name'] = $this->m($msg_code);
			$parse['query']['msg_code'] = $msg_code;
			$parse['query']['msg_args'] = $msg_args;
			$parse['query']['ajax'] = null;
			$parse['query']['r'] = mt_rand();
			$parse['query'] = merge_string($parse['query']);
			$to = merge_url($parse);
		}

		// ajax
		$this->Ajax->is && $this->Ajax->run((array) $arr + ['msg' => $msg, 'msg_code' => $msg_code, 'msg_args' => $msg_args, 'err' => false, 'err_code' => false, 'err_args' => [], 'to' => $to]);

		// header
		@header('location: '. $to);
		exit;
	}


	public function err($a, $to = false, $arr = []) {
		http_no_cache();
		@ob_clean();
		$a = $a ? (array) $a : [0];
		$err_code = reset($a);
		$err_args = $a;
		unset($err_args[key($a)]);
		$err = $this->e($a);

		@header('Content-Err-Code: ' . $err_code);
		@header('Content-Err: ' . $err);

		if ($to && $to !== true) {
			$parse = parse_url($to);
			$parse['scheme'] = empty($parse['scheme']) ? (is_ssl() ? 'https' : 'http') : $parse['scheme'];
			$parse['query'] = empty($parse['query']) ? [] : parse_string($parse['query']);
			$parse['query']['msg'] = null;
			$parse['query']['msg_name'] = null;
			$parse['query']['msg_code'] = null;
			$parse['query']['msg_args'] = null;
			$parse['query']['err'] = $err;
			$parse['query']['err_name'] = $this->e($err_code);
			$parse['query']['err_code'] = $err_code;
			$parse['query']['err_args'] = $err_args;
			$parse['query']['ajax'] = null;
			$parse['query']['r'] = mt_rand();
			$parse['query'] = merge_string($parse['query']);
			$to = merge_url($parse);
		}

		// ajax
		$this->Ajax->is && $this->Ajax->run((array) $arr + ['err' => $err, 'err_code' => $err_code, 'err_args' => $err_args, 'msg' => false, 'msg_code' => false, 'msg_args' => [], 'to' => $to]);

		// 自动重定向的
		if ($to && $to !== true) {
			@header('location: '. $to);
			exit;
		}

		if ($to === true) {
			$to = 'javascript:history.back()';
		}

		$e = '';
		$e .= '<!DOCTYPE html>';
		$e .= '<html xmlns="http://www.w3.org/1999/xhtml">';
		$e .= '<head>';
		$e .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		$e .= '<meta name="robots" content="noindex,nofollow" />';
		if (is_mobile()) {
			$e .= '<meta name="viewport" content="width=device-width" />';
		}
		$e .= "<title>" . $this->Lang('error') . "</title>";
		$e .= '<style type="text/css">';
		$e .= 'html{background: #f9f9f9;}';
		$e .= 'body,input,button,select,textarea{font: medium/1.8em Tahoma,Helvetica,arial,sans-serif;color:#444;}';
		$e .= 'body,div,ul,ol,li,dl,dd,p,h1,h2,h3,h4,h5,h6,form,fieldset,button,input,a,hr{margin:0;padding:0;}';
		if (is_mobile()) {
			$e .= '#err{background: #fff;color: #444;font-family: sans-serif;width: 70%;height:70%;margin: 10% auto 0 auto;padding: 2.0em;border: 1px solid #dfdfdf;}';
		} else {
			$e .= '#err{background: #fff;color: #444;font-family: sans-serif;width: 700px;margin: 10% auto 0 auto;padding: 2.5em;border: 1px solid #dfdfdf;}';
		}
		$e .= '#err .to{margin-top:0.4em;}';
		$e .= '#err .to a{text-decoration: none;font-weight: bold;color: #369;}';
		$e .= '#err .to a:hover{text-decoration: underline;}';
		$e .= '#err .to a:active{color: #D54E21;}';
		$e .= '</style>';
		$e .= '</head>';
		$e .= '<body>';
		$e .= '<div id="err" err_code="'. htmlspecialchars($err_code, ENT_QUOTES) .'">';
		$e .= '<p>' . $err . '</p>';
		if ($to) {
			$e .= '<p class="to"><a href="'. $to .'">'. $this->Lang('return'). '</a></p>';
		}
		$e .= "</div>";
		$e .= "</body>";
		$e .= "</html>";
		exit($e);
	}
}
return new Exit_;
